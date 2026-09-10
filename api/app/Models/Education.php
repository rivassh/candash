<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    use HasFactory;

    protected $table = 'educations';

    protected $fillable = [
        'candidate_id', 'degree', 'field_of_study',
        'institution', 'graduation_year', 'confidence', 'source',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'confidence' => 'float',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public static array $degreeRank = [
        'phd' => 5, 'دکتری' => 5,
        'master' => 4, 'کارشناسی ارشد' => 4, 'فوق لیسانس' => 4,
        'bachelor' => 3, 'کارشناسی' => 3, 'لیسانس' => 3,
        'associate' => 2, 'کاردانی' => 2,
        'high_school' => 1, 'دیپلم' => 1,
    ];
}