<?php

namespace App\Enums;

enum JobPositionStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::Open => 'باز',
            self::Closed => 'بسته',
            self::Archived => 'آرشیو',
        };
    }
}