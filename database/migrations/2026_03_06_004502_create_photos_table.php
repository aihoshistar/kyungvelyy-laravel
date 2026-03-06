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
            $table->foreignId('post_id')->constrained()->cascadeOnDelete(); // Post와 연결
            $table->json('title');               // 개별 사진 다국어 제목
            $table->string('url');               // 이미지 URL 경로
            $table->decimal('aspect_ratio', 5, 3)->nullable(); // 종횡비 (예: 1.5, 0.667)
            $table->json('description')->nullable(); // 다국어 설명

            // EXIF 데이터 (검색 및 정렬 최적화를 위한 숫자형 분리)
            $table->string('exif_body')->nullable();            // NIKON Z f
            $table->string('exif_lens')->nullable();            // NIKKOR Z 40mm f/2
            $table->integer('exif_focal_length')->nullable();   // 40 (단위 제외)
            $table->decimal('exif_aperture', 4, 1)->nullable(); // 4.0 (f/ 제외)
            $table->integer('exif_shutter_speed_num')->nullable(); // 1 (분자)
            $table->integer('exif_shutter_speed_den')->nullable(); // 640 (분모)
            $table->integer('exif_iso')->nullable();            // 1400

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
