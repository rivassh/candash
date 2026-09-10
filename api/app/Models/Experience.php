<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'company', 'job_title', 'start_date',
        'end_date', 'is_current', 'responsibilities', 'confidence', 'source',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'responsibilities' => 'array',
        'confidence' => 'float',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function getDurationMonths(): int
    {
        $end = $this->end_date ?? now();
        return $this->start_date->diffInMonths($end);
    }

    public function getDurationYears(): float
    {
        return round($this->getDurationMonths() / 12, 1);
    }
}