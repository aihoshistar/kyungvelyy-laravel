<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    use HasFactory;

    /**
     * 대량 할당이 가능한 속성 목록입니다.
     * 15년 차의 팁: 항상 명시적인 fillable을 사용하여 보안 취약점을 방어하세요.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'photo_id',
        'type',
        'fingerprint',
    ];

    /**
     * 반응이 속한 사진과의 관계를 정의합니다.
     * * @return BelongsTo
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }
}
