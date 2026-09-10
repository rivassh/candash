<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Pending = 'pending';
    case Reviewed = 'reviewed';
    case Shortlisted = 'shortlisted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار بررسی',
            self::Reviewed => 'بررسی شده',
            self::Shortlisted => 'تایید اولیه',
            self::Rejected => 'رد شده',
        };
    }
}