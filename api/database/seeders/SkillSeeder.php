<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['name' => 'PHP',         'normalized_name' => 'php',          'aliases' => ['php'],                       'category' => 'Backend'],
            ['name' => 'Laravel',    'normalized_name' => 'laravel',     'aliases' => ['laravel', 'لاراول'],         'category' => 'Backend'],
            ['name' => 'Symfony',    'normalized_name' => 'symfony',     'aliases' => ['symfony'],                   'category' => 'Backend'],
            ['name' => 'Python',     'normalized_name' => 'python',      'aliases' => ['python', 'پایتون'],          'category' => 'Backend'],
            ['name' => 'Django',     'normalized_name' => 'django',      'aliases' => ['django'],                    'category' => 'Backend'],
            ['name' => 'Node.js',    'normalized_name' => 'node.js',      'aliases' => ['nodejs', 'node'],             'category' => 'Backend'],
            ['name' => 'Express',    'normalized_name' => 'express',     'aliases' => ['express'],                   'category' => 'Backend'],
            ['name' => 'NestJS',     'normalized_name' => 'nestjs',      'aliases' => ['nest', 'nestjs'],             'category' => 'Backend'],
            ['name' => 'Java',       'normalized_name' => 'java',        'aliases' => ['java', 'جاوا'],               'category' => 'Backend'],
            ['name' => 'Spring Boot','normalized_name' => 'spring boot', 'aliases' => ['spring'],                    'category' => 'Backend'],

            ['name' => 'Vue',        'normalized_name' => 'vue',         'aliases' => ['vue', 'vue.js', 'ویو'],      'category' => 'Frontend'],
            ['name' => 'Nuxt',      'normalized_name' => 'nuxt',         'aliases' => ['nuxt', 'nuxt.js', 'ناکست'],   'category' => 'Frontend'],
            ['name' => 'React',      'normalized_name' => 'react',        'aliases' => ['react', 'ری‌اکت'],            'category' => 'Frontend'],
            ['name' => 'Next.js',    'normalized_name' => 'next.js',      'aliases' => ['next', 'nextjs'],             'category' => 'Frontend'],
            ['name' => 'Angular',    'normalized_name' => 'angular',      'aliases' => ['angular', 'انگولار'],        'category' => 'Frontend'],
            ['name' => 'TypeScript', 'normalized_name' => 'typescript',   'aliases' => ['ts', 'تایپ‌اسکریپت'],         'category' => 'Frontend'],
            ['name' => 'Tailwind',   'normalized_name' => 'tailwind',     'aliases' => ['tailwind css', 'تیلویند'],    'category' => 'Frontend'],
            ['name' => 'HTML/CSS',   'normalized_name' => 'html/css',     'aliases' => ['html', 'css', 'html/css'],    'category' => 'Frontend'],

            ['name' => 'PostgreSQL', 'normalized_name' => 'postgresql',   'aliases' => ['postgres', 'pgsql'],           'category' => 'Database'],
            ['name' => 'MySQL',     'normalized_name' => 'mysql',        'aliases' => ['mysql', 'مای‌اس‌کیوال'],       'category' => 'Database'],
            ['name' => 'MongoDB',   'normalized_name' => 'mongodb',      'aliases' => ['mongo'],                      'category' => 'Database'],
            ['name' => 'Redis',     'normalized_name' => 'redis',        'aliases' => ['redis'],                      'category' => 'Database'],
            ['name' => 'Elasticsearch','normalized_name' => 'elasticsearch','aliases' => ['elastic', 'es'],            'category' => 'Database'],

            ['name' => 'Docker',     'normalized_name' => 'docker',       'aliases' => ['docker', 'داکر'],             'category' => 'DevOps'],
            ['name' => 'Kubernetes', 'normalized_name' => 'kubernetes',   'aliases' => ['k8s', 'کوبرنتیز'],           'category' => 'DevOps'],
            ['name' => 'Terraform',  'normalized_name' => 'terraform',    'aliases' => ['tf', 'تیرافرم'],             'category' => 'DevOps'],
            ['name' => 'AWS',       'normalized_name' => 'aws',          'aliases' => ['amazon web services', 'ای‌دبلیواس'], 'category' => 'DevOps'],
            ['name' => 'GCP',       'normalized_name' => 'gcp',          'aliases' => ['google cloud'],               'category' => 'DevOps'],
            ['name' => 'Ansible',   'normalized_name' => 'ansible',      'aliases' => ['ansible'],                    'category' => 'DevOps'],
            ['name' => 'CI/CD',     'normalized_name' => 'ci/cd',        'aliases' => ['gitlab ci', 'github actions', 'jenkins'], 'category' => 'DevOps'],

            ['name' => 'RabbitMQ',  'normalized_name' => 'rabbitmq',     'aliases' => ['rabbit', 'mq'],               'category' => 'Messaging'],
            ['name' => 'Kafka',     'normalized_name' => 'kafka',        'aliases' => ['apache kafka'],                'category' => 'Messaging'],
            ['name' => 'Nginx',     'normalized_name' => 'nginx',         'aliases' => ['nginx', 'انجین‌اکس'],          'category' => 'DevOps'],

            ['name' => 'Airflow',   'normalized_name' => 'airflow',      'aliases' => ['apache airflow'],             'category' => 'Data'],
            ['name' => 'Spark',     'normalized_name' => 'spark',        'aliases' => ['apache spark'],               'category' => 'Data'],
            ['name' => 'dbt',       'normalized_name' => 'dbt',          'aliases' => ['dbt'],                        'category' => 'Data'],

            ['name' => 'Git',       'normalized_name' => 'git',          'aliases' => ['git', 'گیت'],                 'category' => 'Tools'],
            ['name' => 'REST API',  'normalized_name' => 'rest api',     'aliases' => ['rest', 'restful'],             'category' => 'Tools'],
            ['name' => 'GraphQL',   'normalized_name' => 'graphql',      'aliases' => ['graphql', 'گراف‌کیوال'],       'category' => 'Tools'],
            ['name' => 'Microservices','normalized_name' => 'microservices','aliases' => ['micro', 'میکروسرویس'],  'category' => 'Architecture'],
            ['name' => 'System Design','normalized_name' => 'system design','aliases' => ['system design'],           'category' => 'Architecture'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['normalized_name' => $skill['normalized_name']],
                $skill
            );
        }
    }
}