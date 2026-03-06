<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();    // 고유 ID (예: 2026-03-05_hipjiros...)
            $table->string('category');          // Cityscape, Landscape 등
            $table->json('title');               // 다국어 제목 {ko, en, ja}
            $table->date('taken_at');            // 촬영일
            $table->json('location');            // 다국어 위치 정보 {ko, en, ja}
            $table->string('country');           // 촬영 국가
            $table->decimal('lat', 10, 7)->nullable(); // 위도 (공간 인덱스 활용 가능)
            $table->decimal('lng', 10, 7)->nullable(); // 경도
            $table->string('camera_brand');      // 대표 카메라 브랜드
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
