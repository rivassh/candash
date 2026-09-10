<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ResumeStatus;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'file_path', 'raw_text',
        'parsed_data', 'status', 'confidence',
    ];

    protected $casts = [
        'status' => ResumeStatus::class,
        'parsed_data' => 'array',
        'confidence' => 'float',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}