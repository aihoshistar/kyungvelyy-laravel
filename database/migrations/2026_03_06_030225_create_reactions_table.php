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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'love', 'fire', 'inspired', 'calm'
            $table->string('fingerprint')->index(); // 사용자 브라우저 식별값
            $table->timestamps();

            // 한 사용자가 한 사진에 동일한 반응을 중복해서 남기지 못하도록 유니크 키 설정
            $table->unique(['photo_id', 'fingerprint', 'type']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
