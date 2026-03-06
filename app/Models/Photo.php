<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    /**
     * 대량 할당(Mass Assignment)이 가능한 속성들입니다.
     */
    protected $fillable = [
        'post_id',
        'title',
        'url',
        'aspect_ratio',
        'description',
        'exif_body',
        'exif_lens',
        'exif_focal_length',
        'exif_aperture',
        'exif_shutter_speed_num',
        'exif_shutter_speed_den',
        'exif_iso',
    ];

    /**
     * 속성에 대한 캐스팅을 정의합니다.
     */
    protected $casts = [
        'title'         => 'array',
        'description'   => 'array',
        'aspect_ratio'  => 'decimal:3',
        'exif_aperture' => 'decimal:1',
    ];

    /**
     * Photo가 속한 Post와의 관계를 정의합니다.
     * * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * [Accessor] 프론트엔드 출력을 위한 포맷된 셔터스피드
     * 사용법: $photo->formatted_shutter_speed (예: "1/640s")
     */
    public function getFormattedShutterSpeedAttribute(): ?string
    {
        if ($this->exif_shutter_speed_num && $this->exif_shutter_speed_den) {
            return "{$this->exif_shutter_speed_num}/{$this->exif_shutter_speed_den}s";
        }
        return null;
    }

    /**
     * [Accessor] 프론트엔드 출력을 위한 포맷된 조리개 값
     * 사용법: $photo->formatted_aperture (예: "f/4.0" 또는 "f/4")
     */
    public function getFormattedApertureAttribute(): ?string
    {
        if ($this->exif_aperture) {
            // 소수점 처리를 깔끔하게 하기 위해 float로 형변환
            return "f/" . (float) $this->exif_aperture;
        }
        return null;
    }

    public function reactions() {
        return $this->hasMany(Reaction::class);
    }

    // 사진별 반응 타입별 갯수를 리턴하는 속성
    public function getReactionCountsAttribute() {
        return $this->reactions()->select('type', \DB::raw('count(*) as count'))
                    ->groupBy('type')->pluck('count', 'type');
    }

}
