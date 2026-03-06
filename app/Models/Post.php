<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
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
     * JSON 컬럼을 array로 자동 변환하여 코드에서 다루기 쉽게 만듭니다.
     */
    protected $casts = [
        'title'    => 'array',
        'location' => 'array',
        'taken_at' => 'date',
        'lat'      => 'decimal:7',
        'lng'      => 'decimal:7',
    ];

    /**
     * Post와 Photo의 1:N 관계를 정의합니다.
     * * @return HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
