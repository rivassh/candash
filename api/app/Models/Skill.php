<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'normalized_name', 'aliases', 'category', 'is_active',
    ];

    protected $casts = [
        'aliases' => 'array',
        'is_active' => 'boolean',
    ];

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_skill')
            ->withPivot(['years_experience', 'confidence']);
    }

    public function jobPositions(): BelongsToMany
    {
        return $this->belongsToMany(JobPosition::class, 'job_position_skill');
    }

    public static function findByName(string $name): ?self
    {
        $normalized = static::normalize($name);
        return static::where('normalized_name', $normalized)
            ->orWhereJsonContains('aliases', $name)
            ->first();
    }

    public static function normalize(string $name): string
    {
        return mb_strtolower(trim($name));
    }
}