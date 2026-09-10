<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Experience;
use App\Models\Education;
use App\Models\Skill;
use App\Models\Resume;
use App\Enums\CandidateStatus;
use App\Enums\ResumeStatus;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = $this->candidates();

        foreach ($candidates as $data) {
            $candidate = Candidate::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'linkedin_url' => $data['linkedin_url'] ?? null,
                    'status' => $data['status'] ?? CandidateStatus::New,
                    'summary' => $data['summary'] ?? null,
                ]
            );

            foreach ($data['experiences'] as $exp) {
                $candidate->experiences()->firstOrCreate(
                    [
                        'candidate_id' => $candidate->id,
                        'company' => $exp['company'],
                        'job_title' => $exp['job_title'],
                    ],
                    $exp
                );
            }

            foreach ($data['educations'] as $edu) {
                $candidate->educations()->firstOrCreate(
                    [
                        'candidate_id' => $candidate->id,
                        'institution' => $edu['institution'],
                        'field_of_study' => $edu['field_of_study'],
                    ],
                    $edu
                );
            }

            foreach ($data['skills'] as $skillName => $years) {
                $skill = Skill::findByName($skillName);
                if ($skill && ! $candidate->skills()->where('skill_id', $skill->id)->exists()) {
                    $candidate->skills()->attach($skill->id, [
                        'years_experience' => $years,
                        'confidence' => 0.9,
                    ]);
                }
            }

            if (isset($data['resume_text'])) {
                $candidate->resumes()->firstOrCreate(
                    ['candidate_id' => $candidate->id],
                    [
                        'raw_text' => $data['resume_text'],
                        'parsed_data' => null,
                        'status' => ResumeStatus::Uploaded,
                        'confidence' => 0.85,
                    ]
                );
            }
        }
    }

    protected function candidates(): array
    {
        return [
            [
                'name' => 'علی محمدی',
                'email' => 'ali.mohammadi@example.com',
                'phone' => '+98 912 111 2233',
                'linkedin_url' => 'https://linkedin.com/in/ali-mohammadi-dev',
                'status' => CandidateStatus::InReview,
                'summary' => 'توسعه‌دهنده بک‌اند با ۶ سال تجربه در PHP و Laravel.',
                'experiences' => [
                    ['company' => 'شرکت فناوری پارس', 'job_title' => 'Senior Backend Developer', 'start_date' => '1398-01-01', 'end_date' => null, 'is_current' => true, 'confidence' => 0.9, 'source' => 'resume'],
                    ['company' => 'استارتاپ نوآوران', 'job_title' => 'Backend Developer', 'start_date' => '1396-01-01', 'end_date' => '1398-01-01', 'is_current' => false, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'educations' => [
                    ['degree' => 'کارشناسی ارشد', 'field_of_study' => 'مهندسی کامپیوتر', 'institution' => 'دانشگاه تهران', 'graduation_year' => 1395, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'skills' => ['PHP' => 6, 'Laravel' => 5, 'PostgreSQL' => 5, 'Docker' => 3, 'Redis' => 3, 'REST API' => 5, 'Git' => 4, 'Nginx' => 2],
                'resume_text' => "نام: علی محمدی\nعنوان: Senior Backend Developer\nخلاصه: توسعه‌دهنده بک‌اند با ۶ سال تجربه.\nمهارت‌ها:\n- PHP (۶ سال تجربه)\n- Laravel (۵ سال تجربه)\n- PostgreSQL (۵ سال تجربه)\n- Docker (۳ سال تجربه)\n- Redis (۳ سال تجربه)\n- REST API (۵ سال تجربه)\n- Git (۴ سال تجربه)\n- Nginx (۲ سال تجربه)",
            ],
            [
                'name' => 'مریم حسینی',
                'email' => 'maryam.hosseini@example.com',
                'phone' => '+98 912 333 4455',
                'status' => CandidateStatus::New,
                'summary' => 'توسعه‌دهنده فرانت‌اند با تجربه در Vue و Nuxt.',
                'experiences' => [
                    ['company' => 'آژانس دیجی‌مارک', 'job_title' => 'Frontend Developer', 'start_date' => '1400-06-01', 'end_date' => null, 'is_current' => true, 'confidence' => 0.9, 'source' => 'resume'],
                    ['company' => 'استارتاپ وبینو', 'job_title' => 'Junior Developer', 'start_date' => '1399-01-01', 'end_date' => '1400-06-01', 'is_current' => false, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'educations' => [
                    ['degree' => 'کارشناسی', 'field_of_study' => 'علوم کامپیوتر', 'institution' => 'دانشگاه صنعتی شریف', 'graduation_year' => 1398, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'skills' => ['Vue' => 4, 'Nuxt' => 3, 'Tailwind' => 3, 'TypeScript' => 3, 'React' => 2, 'REST API' => 3, 'Git' => 3],
            ],
            [
                'name' => 'رضا کریمی',
                'email' => 'reza.karimi@example.com',
                'phone' => '+98 912 555 6677',
                'status' => CandidateStatus::Shortlisted,
                'summary' => 'مهندس داده با ۴ سال تجربه در Python و Airflow.',
                'experiences' => [
                    ['company' => 'شرکت داده‌پردازان', 'job_title' => 'Data Engineer', 'start_date' => '1399-01-01', 'end_date' => null, 'is_current' => true, 'confidence' => 0.9, 'source' => 'resume'],
                    ['company' => 'استارتاپ دیتاماین', 'job_title' => 'Junior Developer', 'start_date' => '1397-01-01', 'end_date' => '1399-01-01', 'is_current' => false, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'educations' => [
                    ['degree' => 'کارشناسی ارشد', 'field_of_study' => 'مهندسی داده', 'institution' => 'دانشگاه امیرکبیر', 'graduation_year' => 1396, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'skills' => ['Python' => 5, 'PostgreSQL' => 4, 'Airflow' => 3, 'Spark' => 2, 'Kafka' => 2, 'dbt' => 2, 'Docker' => 3, 'Elasticsearch' => 2],
            ],
            [
                'name' => 'نگار احمدی',
                'email' => 'negar.ahmadi@example.com',
                'phone' => '+98 912 777 8899',
                'status' => CandidateStatus::New,
                'summary' => 'برنامه‌نویس جونیور با انگیزه بالا.',
                'experiences' => [
                    ['company' => 'شرکت طراحی سایت', 'job_title' => 'Junior PHP Developer', 'start_date' => '1403-01-01', 'end_date' => null, 'is_current' => true, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'educations' => [
                    ['degree' => 'کارشناسی', 'field_of_study' => 'مهندسی کامپیوتر', 'institution' => 'دانشگاه شهید بهشتی', 'graduation_year' => 1402, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'skills' => ['PHP' => 1, 'Laravel' => 1, 'Git' => 1, 'MySQL' => 1, 'REST API' => 1],
            ],
            [
                'name' => 'حسین رضایی',
                'email' => 'hossein.rezaei@example.com',
                'phone' => '+98 912 999 0011',
                'linkedin_url' => 'https://linkedin.com/in/hossein-rezaei-devops',
                'status' => CandidateStatus::InReview,
                'summary' => 'مهندس DevOps با ۷ سال تجربه.',
                'experiences' => [
                    ['company' => 'شرکت ابرآروان', 'job_title' => 'Senior DevOps Engineer', 'start_date' => '1397-01-01', 'end_date' => null, 'is_current' => true, 'confidence' => 0.9, 'source' => 'resume'],
                    ['company' => 'استارتاپ پارس', 'job_title' => 'DevOps Engineer', 'start_date' => '1395-01-01', 'end_date' => '1397-01-01', 'is_current' => false, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'educations' => [
                    ['degree' => 'کارشناسی', 'field_of_study' => 'مهندسی کامپیوتر', 'institution' => 'دانشگاه تهران', 'graduation_year' => 1394, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'skills' => ['Docker' => 6, 'Kubernetes' => 5, 'Terraform' => 4, 'AWS' => 3, 'GCP' => 2, 'CI/CD' => 5, 'Ansible' => 3, 'Nginx' => 4],
            ],
            [
                'name' => 'سارا نوری',
                'email' => 'sara.nouri@example.com',
                'phone' => '+98 912 123 4567',
                'status' => CandidateStatus::New,
                'summary' => 'توسعه‌دهنده فول‌استک با تجربه در PHP و Vue.',
                'experiences' => [
                    ['company' => 'شرکت نرم‌افزاری پارس', 'job_title' => 'Full Stack Developer', 'start_date' => '1400-01-01', 'end_date' => null, 'is_current' => true, 'confidence' => 0.9, 'source' => 'resume'],
                    ['company' => 'استارتاپ فینووا', 'job_title' => 'Junior Developer', 'start_date' => '1399-01-01', 'end_date' => '1400-01-01', 'is_current' => false, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'educations' => [
                    ['degree' => 'کارشناسی', 'field_of_study' => 'مهندسی نرم‌افزار', 'institution' => 'دانشگاه علم و صنعت', 'graduation_year' => 1398, 'confidence' => 0.9, 'source' => 'resume'],
                ],
                'skills' => ['PHP' => 4, 'Laravel' => 3, 'Vue' => 3, 'PostgreSQL' => 3, 'Docker' => 2, 'Git' => 3, 'REST API' => 3],
            ],
        ];
    }
}