<?php

namespace Database\Seeders;

use App\Models\JobPosition;
use App\Enums\JobPositionStatus;
use App\Enums\JobLevel;
use App\Enums\EmploymentType;
use Illuminate\Database\Seeder;

class JobPositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            [
                'title' => 'Senior Backend Developer (Laravel)',
                'department' => 'Engineering',
                'level' => JobLevel::Senior,
                'employment_type' => EmploymentType::FullTime,
                'min_experience_years' => 5,
                'education_requirements' => 'کارشناسی مهندسی کامپیوتر',
                'description' => 'طراحی و توسعه سرویس‌های بک‌اند مقیاس‌پذیر با Laravel، بهینه‌سازی کارایی و پایداری سیستم.',
                'status' => JobPositionStatus::Open,
                'required_skills' => [
                    ['name' => 'PHP',         'weight' => 9, 'min_years' => 5],
                    ['name' => 'Laravel',     'weight' => 9, 'min_years' => 4],
                    ['name' => 'PostgreSQL',  'weight' => 7, 'min_years' => 3],
                    ['name' => 'Docker',      'weight' => 6, 'min_years' => 2],
                    ['name' => 'Redis',       'weight' => 6, 'min_years' => 2],
                    ['name' => 'REST API',   'weight' => 7, 'min_years' => 3],
                ],
                'preferred_skills' => ['Kubernetes', 'Microservices', 'RabbitMQ', 'System Design'],
            ],
            [
                'title' => 'Frontend Developer (Nuxt/Vue)',
                'department' => 'Engineering',
                'level' => JobLevel::Mid,
                'employment_type' => EmploymentType::FullTime,
                'min_experience_years' => 3,
                'education_requirements' => 'کارشناسی',
                'description' => 'توسعه رابط کاربری فارسی و راست‌چین با Nuxt 3 و Tailwind CSS.',
                'status' => JobPositionStatus::Open,
                'required_skills' => [
                    ['name' => 'Vue',         'weight' => 8, 'min_years' => 3],
                    ['name' => 'Nuxt',        'weight' => 7, 'min_years' => 2],
                    ['name' => 'Tailwind',    'weight' => 6, 'min_years' => 2],
                    ['name' => 'TypeScript',  'weight' => 7, 'min_years' => 2],
                    ['name' => 'REST API',   'weight' => 5, 'min_years' => 1],
                ],
                'preferred_skills' => ['Pinia', 'i18n', 'RTL Layouts'],
            ],
            [
                'title' => 'DevOps Engineer',
                'department' => 'Platform',
                'level' => JobLevel::Senior,
                'employment_type' => EmploymentType::FullTime,
                'min_experience_years' => 5,
                'education_requirements' => 'کارشناسی',
                'description' => 'مدیریت زیرساخت ابری، CI/CD و Kubernetes.',
                'status' => JobPositionStatus::Open,
                'required_skills' => [
                    ['name' => 'Docker',      'weight' => 8, 'min_years' => 3],
                    ['name' => 'Kubernetes',  'weight' => 9, 'min_years' => 3],
                    ['name' => 'Terraform',    'weight' => 7, 'min_years' => 2],
                    ['name' => 'CI/CD',       'weight' => 7, 'min_years' => 2],
                ],
                'preferred_skills' => ['AWS', 'GCP', 'Ansible', 'Prometheus'],
            ],
            [
                'title' => 'Junior PHP Developer',
                'department' => 'Engineering',
                'level' => JobLevel::Junior,
                'employment_type' => EmploymentType::FullTime,
                'min_experience_years' => 0,
                'education_requirements' => 'کاردانی',
                'description' => 'همکاری در توسعه و تست API ها با PHP و Laravel.',
                'status' => JobPositionStatus::Open,
                'required_skills' => [
                    ['name' => 'PHP',        'weight' => 7, 'min_years' => 0],
                    ['name' => 'Laravel',    'weight' => 6, 'min_years' => 0],
                    ['name' => 'Git',        'weight' => 5, 'min_years' => 0],
                ],
                'preferred_skills' => ['Docker', 'REST API', 'MySQL'],
            ],
            [
                'title' => 'Data Engineer',
                'department' => 'Data',
                'level' => JobLevel::Mid,
                'employment_type' => EmploymentType::FullTime,
                'min_experience_years' => 3,
                'education_requirements' => 'کارشناسی ارشد',
                'description' => 'ساخت خطوط پردازش داده، انبار داده و ETL.',
                'status' => JobPositionStatus::Open,
                'required_skills' => [
                    ['name' => 'Python',     'weight' => 8, 'min_years' => 3],
                    ['name' => 'PostgreSQL', 'weight' => 7, 'min_years' => 3],
                    ['name' => 'Airflow',    'weight' => 6, 'min_years' => 2],
                ],
                'preferred_skills' => ['Spark', 'dbt', 'Kafka', 'Elasticsearch'],
            ],
        ];

        foreach ($positions as $pos) {
            JobPosition::firstOrCreate(
                ['title' => $pos['title']],
                $pos
            );
        }
    }
}