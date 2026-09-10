<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MatchStatus;

class MatchResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'job_position_id', 'total_score',
        'breakdown', 'strengths', 'gaps', 'status', 'notes',
    ];

    protected $casts = [
        'total_score' => 'float',
        'breakdown' => 'array',
        'strengths' => 'array',
        'gaps' => 'array',
        'status' => MatchStatus::class,
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id');
    }
}