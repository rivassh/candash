<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Internship = 'internship';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'تمام‌وقت',
            self::PartTime => 'پاره‌وقت',
            self::Contract => 'قراردادی',
            self::Internship => 'کارآموزی',
        };
    }
}