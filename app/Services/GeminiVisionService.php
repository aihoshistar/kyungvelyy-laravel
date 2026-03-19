<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GeminiVisionService
{
    public function analyzeImage(UploadedFile $file, string $locationHint, string $geminiKey)
    {
        // 1. 이미지 매니저 초기화 및 리사이징 (폭 1024px)
        $manager = new ImageManager(new Driver());
        $aiImage = $manager->read($file->getRealPath())
            ->scaleDown(width: 1024);

        // 2. Base64 인코딩 (압축 전송)
        $base64Image = base64_encode($aiImage->toJpeg(70)->toString());

        // 3. AI 프롬프트 (기존 AdminController의 프롬프트와 동일하게 유지)
        $prompt = "You are a professional photographer's assistant. [User Input Location]: {$locationHint}. Analyze the provided image and generate metadata.
[Guidelines]
1. Tone & Style: Write the 'description' in a calm, professional, and artistic documentary style.
2. Korean Language Rule: For the 'ko' description, ALWAYS end sentences with formal written style endings such as '~이다.', '~한다.', '~다.' (Do NOT use '~습니다' or '~해요').
3. Accuracy: Base the description strictly on the visual elements of the image and the provided location hint.

The response MUST be a valid JSON object matching exactly this structure:
{
    \"title\": {\"ko\": \"\", \"en\": \"\", \"ja\": \"\"},
    \"location\": {\"ko\": \"\", \"en\": \"\", \"ja\": \"\"},
    \"description\": {\"ko\": \"\", \"en\": \"\", \"ja\": \"\"},
    \"category\": \"Landscape|Cityscape|Portrait\",
    \"country\": \"English Country Name\"
}";

        // 4. Gemini API 호출 (Timeout 120초 설정)
        $response = Http::timeout(120)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key={$geminiKey}", [
            'contents' => [
                ['parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $base64Image]]
                ]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("Gemini API 오류: " . $response->json('error.message', '알 수 없는 API 에러 발생'));
        }

        $jsonText = $response->json('candidates.0.content.parts.0.text', '{}');
        $aiData = json_decode(trim($jsonText), true);

        if (!$aiData || !isset($aiData['title'])) {
            throw new \Exception("Gemini 응답을 JSON으로 변환할 수 없습니다.");
        }

        return $aiData;
    }
}
