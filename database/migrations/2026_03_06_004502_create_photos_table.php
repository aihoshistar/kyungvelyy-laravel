<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->unique(); // 고유 ID (예: 2026-03-05_hipjiros...)
            $table->string('category');          // Cityscape, Landscape 등
            $table->json('title');               // 다국어 제목 {ko, en, ja}
            $table->date('taken_at');            // 촬영일
            $table->json('location');            // 다국어 위치 정보 {ko, en, ja}
            $table->string('country');           // 촬영 국가
            $table->decimal('lat', 10, 7)->nullable(); // 위도 (좌표)
            $table->decimal('lng', 10, 7)->nullable(); // 경도 (좌표)
            $table->string('camera');            // 대표 카메라 브랜드
            $table->json('images');              // 개별 이미지 배열 (다국어 설명 및 EXIF 데이터 포함)
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
