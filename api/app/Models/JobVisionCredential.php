<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobVisionCredential extends Model
{
    protected $table = 'jobvision_credentials';

    protected $fillable = [
        'username',
        'password',
        'captcha_token',
        'cookie',
        'api_url',
        'account_url',
        'job_post_ids',
        'expire_at',
        'is_active',
    ];

    protected $casts = [
        'job_post_ids' => 'array',
        'expire_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isExpired(): bool
    {
        return $this->expire_at !== null && $this->expire_at->isPast();
    }
}