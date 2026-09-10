<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\CandidateStatus;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'linkedin_url',
        'status', 'summary', 'enrichment_data',
    ];

    protected $casts = [
        'status' => CandidateStatus::class,
        'enrichment_data' => 'array',
    ];

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function latestResume(): HasOne
    {
        return $this->hasOne(Resume::class)->latestOfMany();
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'candidate_skill')
            ->withPivot(['years_experience', 'confidence']);
    }

    public function matchResults(): HasMany
    {
        return $this->hasMany(MatchResult::class);
    }
}