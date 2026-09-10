<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case HrSpecialist = 'hr_specialist';
}