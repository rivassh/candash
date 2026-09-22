<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\JobPositionStatus;
use App\Enums\JobLevel;
use App\Enums\EmploymentType;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'department', 'level', 'employment_type',
        'min_experience_years', 'education_requirements', 'description',
        'external_id', 'status', 'required_skills', 'preferred_skills',
    ];

    protected $casts = [
        'level' => JobLevel::class,
        'employment_type' => EmploymentType::class,
        'status' => JobPositionStatus::class,
        'min_experience_years' => 'integer',
        'required_skills' => 'array',
        'preferred_skills' => 'array',
    ];

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'match_results')
            ->withPivot(['total_score', 'breakdown', 'strengths', 'gaps', 'status'])
            ->withTimestamps();
    }

    public function matchResults(): HasMany
    {
        return $this->hasMany(MatchResult::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', JobPositionStatus::Open);
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\JobPositionObserver::class);
    }
}