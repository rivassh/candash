<?php

namespace App\Services\Resume;

use App\Contracts\Resume\ResumeExtractorInterface;
use App\DTOs\Resume\ParsedResumeDto;
use Illuminate\Support\Facades\Log;

/**
 * پیاده‌سازی ساختگی استخراج‌کننده رزومه.
 *
 * این سرویس متون رزومه را با روش‌های فرمولی و قطعی تجزیه می‌کند و نیازی به LLM ندارد.
 * در پروژه واقعی، این کلاس با سرویس هوش مصنوعی جایگزین می‌شود.
 */
class MockResumeExtractor implements ResumeExtractorInterface
{
    public function extract(string $rawTextOrPath): ParsedResumeDto
    {
        $text = $this->loadText($rawTextOrPath);

        return new ParsedResumeDto(
            name:          $this->extractName($text),
            email:         $this->extractEmail($text),
            phone:         $this->extractPhone($text),
            linkedinUrl:   $this->extractLinkedin($text),
            skills:        $this->extractSkills($text),
            experiences:   $this->extractExperiences($text),
            educations:    $this->extractEducations($text),
            summary:       $this->extractSummary($text),
            confidence:    0.87,
        );
    }

    protected function loadText(string $rawTextOrPath): string
    {
        if (is_file($rawTextOrPath) && str_ends_with($rawTextOrPath, '.txt')) {
            return (string) @file_get_contents($rawTextOrPath);
        }
        return $rawTextOrPath;
    }

    protected function extractName(string $text): string
    {
        if (preg_match('/نام:\s*(.+)/u', $text, $m)) {
            return trim($m[1]);
        }
        return 'نامشخص';
    }

    protected function extractEmail(string $text): ?string
    {
        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/u', $text, $m)) {
            return $m[0];
        }
        return null;
    }

    protected function extractPhone(string $text): ?string
    {
        if (preg_match('/(\+?\d[\d\s\-]{7,}\d)/u', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractLinkedin(string $text): ?string
    {
        if (preg_match('/https?:\/\/(www\.)?linkedin\.com\/[\w\-\/]+/u', $text, $m)) {
            return $m[0];
        }
        return null;
    }

    protected function extractSkills(string $text): array
    {
        $skills = [];
        // الگوی: - نام مهارت (X سال تجربه)
        if (preg_match_all('/^-\s*([^\(]+?)\s*\(\s*(\d+(?:\.\d+)?)\s*سال/u', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $skills[] = [
                    'name' => trim($m[1]),
                    'years_experience' => (float) $m[2],
                    'confidence' => 0.90,
                ];
            }
        }
        return $skills;
    }

    protected function extractExperiences(string $text): array
    {
        $experiences = [];
        // الگوی: - شرکت (سال - سال/تاکنون): عنوان
        if (preg_match_all('/^-\s*([^\(]+?)\s*\(([\d۰-۹]{4})\s*-\s*(تاکنون|[\d۰-۹]{4})\)\s*:\s*(.+)$/um', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $start = $this->persianToEnglish($m[2]);
                $end = $m[3] === 'تاکنون' ? null : $this->persianToEnglish($m[3]);

                $startDate = $start ? ($start <= 1400 ? $this->jalaliToGregorianYear((int) $start) : (int) $start) : null;
                $endDate = $end !== null ? ($end <= 1500 ? $this->jalaliToGregorianYear((int) $end) : (int) $end) : null;

                $experiences[] = [
                    'company' => trim($m[1]),
                    'job_title' => trim($m[4]),
                    'start_year_jalali' => (int) $start,
                    'end_year_jalali' => $end !== null ? (int) $end : null,
                    'start_date' => $startDate ? "{$startDate}-01-01" : null,
                    'end_date' => $endDate ? "{$endDate}-12-31" : null,
                    'is_current' => $m[3] === 'تاکنون',
                    'source' => 'resume',
                    'confidence' => 0.85,
                ];
            }
        }
        return $experiences;
    }

    protected function extractEducations(string $text): array
    {
        $educations = [];
        // الگوی: - مقطع رشته، دانشگاه، سال
        if (preg_match_all('/^-\s*([^\،]+?)\s*،\s*([^،]+?)\s*،\s*([^،\n]+)/um', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $line = $m[0];
                if (! str_contains($line, 'دانشگاه') && ! str_contains($line, 'دانشکده')) {
                    continue;
                }
                $year = null;
                if (preg_match('/(\d{4})/u', $m[3], $y)) {
                    $year = (int) $y[1];
                }
                $educations[] = [
                    'degree' => trim($m[1]),
                    'field_of_study' => trim($m[2]),
                    'institution' => trim($m[3]),
                    'graduation_year' => $year,
                    'source' => 'resume',
                    'confidence' => 0.85,
                ];
            }
        }
        return $educations;
    }

    protected function extractSummary(string $text): ?string
    {
        if (preg_match('/خلاصه:\s*(.+)/u', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function persianToEnglish(string $num): int
    {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        return (int) str_replace($persian, $english, $num);
    }

    /**
     * تبدیل سال شمسی به میلادی (تقریبی - فقط برای نمایش).
     */
    protected function jalaliToGregorianYear(int $jy): int
    {
        return $jy - 621;
    }
}