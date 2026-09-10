<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'مدیر سیستم',
            'email' => 'admin@talentmatch.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'مریم احمدی',
            'email' => 'hr@talentmatch.local',
            'password' => Hash::make('hr123456'),
            'role' => 'hr_specialist',
            'is_active' => true,
        ]);
    }
}