<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobSourceCredential extends Model
{
    // SoftDeletes removed for generic credentials storage
    protected $fillable = [
        'provider',
        'name',
        'config',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Deactivate all other active credentials for the same provider.
     */
    public function deactivateOthers(): void
    {
        self::where('provider', $this->provider)
            ->where('id', '!=', $this->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
