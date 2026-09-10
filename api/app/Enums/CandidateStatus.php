<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case New = 'new';
    case InReview = 'in_review';
    case Shortlisted = 'shortlisted';
    case Rejected = 'rejected';
    case Hired = 'hired';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جدید',
            self::InReview => 'در حال بررسی',
            self::Shortlisted => 'تایید اولیه',
            self::Rejected => 'رد شده',
            self::Hired => 'استخدام شده',
        };
    }
}