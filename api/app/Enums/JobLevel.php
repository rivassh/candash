<?php

namespace App\Enums;

enum JobLevel: string
{
    case Junior = 'junior';
    case Mid = 'mid';
    case Senior = 'senior';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Junior => 'جونیور',
            self::Mid => 'میدلول',
            self::Senior => 'سنیور',
        };
    }
}