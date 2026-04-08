<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Post extends Model implements Feedable
{
    use HasFactory;

    /**
     * 대량 할당(Mass Assignment)이 가능한 속성들입니다.
     */
    protected $fillable = [
        'slug',
        'category',
        'title',
        'taken_at',
        'location',
        'country',
        'lat',
        'lng',
        'camera_brand',
    ];

    /**
     * 속성에 대한 캐스팅을 정의합니다.
     * 다국어 지원을 위해 JSON 컬럼들을 배열로 자동 변환합니다.
     */
    protected $casts = [
        'title'       => 'array',
        'location'    => 'array',
        'taken_at'    => 'date',
        'lat'         => 'decimal:7',
        'lng'         => 'decimal:7',
    ];

    /**
     * Post와 Photo의 1:N 관계를 정의합니다.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * [Spatie Feed] RSS 피드에 포함될 데이터 수집 로직입니다.
     * 최신 촬영일 순으로 사진 정보를 포함하여 가져옵니다.
     */
    public static function getFeedItems()
    {
        return static::with('photos')->orderBy('taken_at', 'desc')->get();
    }

    /**
     * [Spatie Feed] 개별 포스트를 RSS FeedItem 객체로 변환합니다.
     * 다국어(KO, EN, JA)를 자동으로 감지하여 처리합니다.
     */
    public function toFeedItem(): FeedItem
    {
        // 1. 현재 피드가 요청된 언어(Locale)를 확인합니다.
        $locale = app()->getLocale();
        $slug = $this->slug ?? $this->id;

        // 2. 다국어 제목 및 위치 데이터 추출 (현재 언어가 없으면 영어를 기본값으로 사용)
        $title = data_get($this->title, $locale) ?? data_get($this->title, 'en') ?? 'Untitled';
        $locName = data_get($this->location, $locale) ?? data_get($this->location, 'en') ?? "";

        // 3. 딥링크 URL 생성 (예: /ko/archive/seoul-trip/1)
        $link = url("/{$locale}/archive/{$slug}/1");

        // 4. 첫 번째 사진 정보를 가져옵니다. (설명 및 이미지 URL 활용)
        $firstPhoto = $this->photos->first();
        $photoUrl = $firstPhoto ? $firstPhoto->url : '';
        
        // 💡 [수정] Post에는 description이 없으므로, Photo 모델에 정의된 description을 가져옵니다.
        $desc = $firstPhoto ? (data_get($firstPhoto->description, $locale) ?? data_get($firstPhoto->description, 'en') ?? "") : "";

        // 5. RSS 리더기용 요약 HTML 구성
        $summary = "";
        if ($photoUrl) {
            $summary .= "<img src=\"{$photoUrl}\" alt=\"{$title}\" style=\"max-width:100%; border-radius:8px;\" /><br /><br />";
        }
        
        if ($desc) {
            $summary .= "<p>{$desc}</p>";
        } elseif ($locName) {
            $summary .= "<p>Photography at {$locName}</p>";
        }

        return FeedItem::create()
            ->id($link) // GUID로 전체 URL을 사용하여 전역 유일성 보장
            ->title($title)
            ->summary($summary)
            ->updated($this->updated_at)
            ->link($link)
            ->authorName('PARK KYOUNG SEO')
            ->category($this->category ?? 'Photography');
    }
}