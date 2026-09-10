<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'user_id', 'model_type', 'model_id',
        'action', 'changes', 'ip_address', 'created_at',
    ];
    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public static function log(string $modelType, int $modelId, string $action, array $changes, ?int $userId = null): static
    {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}