<?php

namespace App\Services\Enrichment;

use App\Contracts\Enrichment\EnrichmentInterface;

/**
 * پیاده‌سازی ساختگی غنی‌سازی پروفایل.
 *
 * در محیط واقعی، این سرویس با API لینکدین (یا سرویس‌های واسط مانند Proxycurl) جایگزین می‌شود.
 */
class MockEnrichmentService implements EnrichmentInterface
{
    public function enrichByLinkedin(string $linkedinUrl, ?string $name = null): array
    {
        // هش پروفایل به عنوان seed برای تولید داده‌های قابل پیش‌بینی
        $seed = crc32($linkedinUrl);
        mt_srand($seed);

        $headlines = [
            'Senior Backend Developer',
            'Full-Stack Engineer',
            'Frontend Developer',
            'Data Engineer',
            'DevOps Engineer',
            'Tech Lead',
        ];

        $companies = [
            'شرکت فناوری پارس', 'استارتاپ نوآوران', 'دیجی‌پی', 'اسنپ', 'تپسی',
            'فناپ', 'همراه اول', 'ایرانسل', 'بانکداری الکترونیک',
        ];

        $allSkills = [
            'PHP', 'Laravel', 'Symfony', 'CodeIgniter',
            'Vue', 'Nuxt', 'React', 'Angular', 'TypeScript',
            'PostgreSQL', 'MySQL', 'MongoDB', 'Redis', 'Elasticsearch',
            'Docker', 'Kubernetes', 'Terraform', 'Ansible',
            'Python', 'Django', 'FastAPI', 'Flask',
            'Node.js', 'Express', 'NestJS',
            'AWS', 'GCP', 'Azure',
            'RabbitMQ', 'Kafka', 'Nginx',
        ];

        // انتخاب 5 تا 12 مهارت به صورت قطعی
        $skillCount = mt_rand(5, 12);
        $shuffled = $allSkills;
        shuffle($shuffled);
        $picked = array_slice($shuffled, 0, $skillCount);

        $skills = [];
        foreach ($picked as $skill) {
            $skills[] = [
                'name' => $skill,
                'years_experience' => mt_rand(1, 8),
                'endorsements' => mt_rand(0, 50),
                'source' => 'linkedin',
                'confidence' => round(0.85 + (mt_rand(0, 10) / 100), 2),
            ];
        }

        $experiences = [];
        $numExperiences = mt_rand(2, 4);
        for ($i = 0; $i < $numExperiences; $i++) {
            $endYear = 1404 - ($i * mt_rand(2, 4));
            $startYear = $endYear - mt_rand(2, 5);
            $experiences[] = [
                'company' => $companies[array_rand($companies)],
                'job_title' => $headlines[array_rand($headlines)],
                'start_year_jalali' => $startYear,
                'end_year_jalali' => $i === 0 ? null : $endYear,
                'is_current' => $i === 0,
                'source' => 'linkedin',
                'confidence' => 0.85,
            ];
        }

        return [
            'linkedin_url' => $linkedinUrl,
            'headline' => $headlines[array_rand($headlines)],
            'summary' => 'توسعه‌دهنده با تجربه در پروژه‌های مقیاس‌پذیر سازمانی.',
            'location' => 'تهران، ایران',
            'skills' => $skills,
            'experiences' => $experiences,
            'connections' => 500 + mt_rand(0, 500),
            'enriched_at' => now()->toIso8601String(),
            'driver' => 'mock',
            'confidence' => round(0.85 + (mt_rand(0, 10) / 100), 2),
        ];
    }

    public function driverName(): string
    {
        return 'mock';
    }
}