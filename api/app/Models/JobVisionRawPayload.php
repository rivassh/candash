<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobVisionRawPayload extends Model
{
    protected $fillable = [
        'endpoint',
        'entity_type',
        'external_id',
        'payload',
        'fetched_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'fetched_at' => 'datetime',
    ];

    public const ENTITY_JOB_POST = 'job_post';
    public const ENTITY_APPLICATION_SUMMARY = 'application_summary';
    public const ENTITY_APPLICATION_HEADER = 'application_header';
    public const ENTITY_APPLICATION_DETAILS = 'application_details';
}
