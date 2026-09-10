<?php

namespace App\Services\Matching;

use App\Models\Candidate;
use App\Models\JobPosition;
use App\Models\MatchResult;
use App\Models\Experience;
use App\Enums\MatchStatus;
use App\Enums\JobLevel;
use Illuminate\Support\Facades\DB;

/**
 * موتور تطبیق استعداد به روش فرمولی، قطعی و قابل توضیح.
 *
 * فرمول نهایی:
 *   total = 0.40 * required_skills
 *         + 0.20 * experience
 *         + 0.15 * seniority
 *         + 0.10 * education
 *         + 0.10 * preferred_skills
 *         + 0.05 * stability
 */
class MatchingService
{
    public function __construct(protected SkillMatcher $skillMatcher) {}

    /**
     * اجرای تطبیق برای یک کاندیدا و یک موقعیت شغلی و ذخیره نتیجه.
     */
    public function run(Candidate $candidate, JobPosition $job): MatchResult
    {
        $breakdown = [
            'required_skills' => $this->scoreRequiredSkills($candidate, $job),
            'experience'      => $this->scoreExperience($candidate, $job),
            'seniority'       => $this->scoreSeniority($candidate, $job),
            'education'       => $this->scoreEducation($candidate, $job),
            'preferred_skills'=> $this->scorePreferredSkills($candidate, $job),
            'stability'       => $this->scoreStability($candidate, $job),
        ];

        $weights = config('talentmatch.matching.weights');

        $totalScore = 0;
        foreach ($breakdown as $key => $data) {
            $totalScore += ($data['score'] / 100) * $weights[$key];
        }
        $totalScore = round($totalScore, 2);

        $strengths = $this->collectStrengths($breakdown, $job);
        $gaps      = $this->collectGaps($breakdown, $job);

        return DB::transaction(function () use ($candidate, $job, $totalScore, $breakdown, $strengths, $gaps) {
            return MatchResult::updateOrCreate(
                ['candidate_id' => $candidate->id, 'job_position_id' => $job->id],
                [
                    'total_score' => $totalScore,
                    'breakdown'   => $breakdown,
                    'strengths'   => $strengths,
                    'gaps'        => $gaps,
                    'status'      => MatchStatus::Pending,
                ]
            );
        });
    }

    /**
     * محاسبه تطابق مهارت‌های الزامی - وزن ۴۰٪
     *
     * @return array{score: float, details: array, matched: array, missing: array}
     */
    public function scoreRequiredSkills(Candidate $candidate, JobPosition $job): array
    {
        $required = collect($job->required_skills ?? []);
        if ($required->isEmpty()) {
            return ['score' => 100, 'details' => [], 'matched' => [], 'missing' => []];
        }

        // ساخت نقشه مهارت‌های کاندیدا
        $candidateSkills = $candidate->skills()->get(['skills.id as skill_id', 'skills.name', 'candidate_skill.years_experience', 'candidate_skill.confidence'])
            ->keyBy('skill_id');

        $totalWeight = 0;
        $earned = 0;
        $details = [];
        $matched = [];
        $missing = [];

        foreach ($required as $req) {
            $name = is_array($req) ? ($req['name'] ?? null) : $req;
            $weight = is_array($req) ? (int) ($req['weight'] ?? 5) : 5;
            $minYears = is_array($req) ? (int) ($req['min_years'] ?? 0) : 0;
            $totalWeight += $weight;

            $skillId = $this->skillMatcher->resolveSkillId((string) $name);
            $candidateYears = 0;
            $found = false;
            if ($skillId && $candidateSkills->has($skillId)) {
                $cs = $candidateSkills->get($skillId);
                $candidateYears = (float) $cs->pivot->years_experience;
                $found = true;
            }

            $score = $this->skillMatcher->scoreSkill($candidateYears, $weight, $minYears);
            $earned += $score * $weight;

            $details[] = [
                'skill' => $name,
                'weight' => $weight,
                'min_years' => $minYears,
                'candidate_years' => $candidateYears,
                'score' => round($score * 100, 1),
                'found' => $found,
            ];

            if ($found && $score >= 0.7) {
                $matched[] = $name;
            } else {
                $missing[] = $name;
            }
        }

        $score = $totalWeight > 0 ? ($earned / $totalWeight) * 100 : 0;
        return [
            'score'   => round($score, 1),
            'details' => $details,
            'matched' => $matched,
            'missing' => $missing,
        ];
    }

    /**
     * محاسبه تطابق سوابق کاری - وزن ۲۰٪
     */
    public function scoreExperience(Candidate $candidate, JobPosition $job): array
    {
        $experiences = $candidate->experiences;
        if ($experiences->isEmpty()) {
            return ['score' => 0, 'total_years' => 0, 'relevant_years' => 0, 'details' => []];
        }

        $jobTitleKeywords = $this->keywordsFromTitle($job->title);

        $totalMonths = 0;
        $relevantMonths = 0;
        $titleMatchMonths = 0;
        $details = [];

        foreach ($experiences as $exp) {
            $months = $exp->getDurationMonths();
            $totalMonths += $months;

            $isTitleMatch = $this->isTitleRelated($exp->job_title, $jobTitleKeywords);
            if ($isTitleMatch) {
                $titleMatchMonths += $months;
                $relevantMonths += $months;
            }

            $details[] = [
                'company' => $exp->company,
                'title' => $exp->job_title,
                'months' => $months,
                'years' => round($months / 12, 1),
                'title_match' => $isTitleMatch,
            ];
        }

        $totalYears = $totalMonths / 12;
        $required = max(1, $job->min_experience_years);

        // امتیاز کل: ۶۰٪ متناسب با نسبت کل به حداقل + ۴۰٪ تطابق عنوان
        $ratioScore = min(1.0, $totalYears / $required);
        $titleScore = $totalMonths > 0 ? ($titleMatchMonths / $totalMonths) : 0;
        $score = (0.6 * $ratioScore + 0.4 * $titleScore) * 100;

        return [
            'score' => round($score, 1),
            'total_years' => round($totalYears, 1),
            'relevant_years' => round($relevantMonths / 12, 1),
            'required_years' => $required,
            'details' => $details,
        ];
    }

    /**
     * محاسبه تطابق سطح ارشدیت - وزن ۱۵٪
     */
    public function scoreSeniority(Candidate $candidate, JobPosition $job): array
    {
        $totalYears = $candidate->experiences->sum(fn($e) => $e->getDurationYears());
        $levels = config('talentmatch.matching.seniority_levels');
        $jobLevel = $job->level?->value ?? JobLevel::Mid->value;

        $expected = $levels[$jobLevel] ?? $levels['mid'];
        $expectedMin = $expected['min_years'];

        // نسبت تجربه کاندیدا به حداقل مورد انتظار
        if ($totalYears >= $expectedMin) {
            $score = 100;
        } elseif ($expectedMin > 0) {
            $score = min(100, ($totalYears / $expectedMin) * 100);
        } else {
            $score = 100;
        }

        return [
            'score' => round($score, 1),
            'candidate_years' => round($totalYears, 1),
            'job_level' => $jobLevel,
            'expected_min_years' => $expectedMin,
        ];
    }

    /**
     * محاسبه تطابق تحصیلات - وزن ۱۰٪
     */
    public function scoreEducation(Candidate $candidate, JobPosition $job): array
    {
        $educations = $candidate->educations;
        $required = strtolower($job->education_requirements ?? '');

        if ($educations->isEmpty()) {
            return ['score' => 0, 'details' => [], 'required' => $required];
        }

        $requiredRank = $this->extractDegreeRank($required);

        $best = 0;
        $details = [];
        foreach ($educations as $edu) {
            $rank = \App\Models\Education::$degreeRank[strtolower($edu->degree)] ?? 0;
            $details[] = [
                'degree' => $edu->degree,
                'field' => $edu->field_of_study,
                'institution' => $edu->institution,
                'year' => $edu->graduation_year,
                'rank' => $rank,
            ];
            if ($rank > $best) {
                $best = $rank;
            }
        }

        if ($requiredRank === 0) {
            $score = $best > 0 ? 100 : 0;
        } else {
            $score = min(100, ($best / $requiredRank) * 100);
        }

        return [
            'score' => round($score, 1),
            'best_degree_rank' => $best,
            'required_rank' => $requiredRank,
            'details' => $details,
        ];
    }

    /**
     * محاسبه تطابق مهارت‌های ترجیحی - وزن ۱۰٪
     */
    public function scorePreferredSkills(Candidate $candidate, JobPosition $job): array
    {
        $preferred = $job->preferred_skills ?? [];
        if (empty($preferred)) {
            return ['score' => 100, 'matched' => [], 'total' => 0];
        }

        $candidateSkillIds = $candidate->skills()->pluck('skills.id')->all();
        $matched = [];
        foreach ($preferred as $name) {
            $skillId = $this->skillMatcher->resolveSkillId($name);
            if ($skillId && in_array($skillId, $candidateSkillIds, true)) {
                $matched[] = $name;
            }
        }
        $score = (count($matched) / count($preferred)) * 100;

        return [
            'score' => round($score, 1),
            'matched' => $matched,
            'total' => count($preferred),
        ];
    }

    /**
     * محاسبه پایداری شغلی - وزن ۵٪
     *
     * بررسی می‌کند که آیا کاندیدا سابقه کار طولانی (بیش از ۲ سال) در هر موقعیت داشته یا خیر.
     */
    public function scoreStability(Candidate $candidate, JobPosition $job): array
    {
        $experiences = $candidate->experiences;
        if ($experiences->isEmpty()) {
            return ['score' => 0, 'long_tenures' => 0, 'total' => 0];
        }

        $longTenures = 0;
        foreach ($experiences as $exp) {
            if ($exp->getDurationYears() >= 2) {
                $longTenures++;
            }
        }
        $total = $experiences->count();
        $score = ($longTenures / $total) * 100;

        return [
            'score' => round($score, 1),
            'long_tenures' => $longTenures,
            'total' => $total,
        ];
    }

    protected function collectStrengths(array $breakdown, JobPosition $job): array
    {
        $items = [];
        $req = $breakdown['required_skills'];
        foreach ($req['matched'] ?? [] as $skill) {
            $items[] = "تسلط بر «{$skill}» مطابق با نیازمندی موقعیت";
        }
        if (($breakdown['experience']['score'] ?? 0) >= 70) {
            $items[] = "سابقه کاری مرتبط ({$breakdown['experience']['total_years']} سال) نزدیک به الزام موقعیت";
        }
        if (($breakdown['seniority']['score'] ?? 0) >= 80) {
            $items[] = "سطح ارشدیت متناسب با موقعیت («{$breakdown['seniority']['job_level']}»)";
        }
        if (($breakdown['education']['score'] ?? 0) >= 80) {
            $items[] = "مدرک تحصیلی مرتبط";
        }
        if (($breakdown['preferred_skills']['score'] ?? 0) >= 50) {
            $items[] = "دارا بودن " . count($breakdown['preferred_skills']['matched'] ?? []) . " مهارت ترجیحی از موقعیت";
        }
        if (($breakdown['stability']['score'] ?? 0) >= 75) {
            $items[] = "سابقه کار طولانی‌مدت (بیش از ۲ سال) در موقعیت‌های قبلی";
        }
        return array_values(array_unique($items));
    }

    protected function collectGaps(array $breakdown, JobPosition $job): array
    {
        $items = [];
        $req = $breakdown['required_skills'];
        foreach ($req['missing'] ?? [] as $skill) {
            $items[] = "نبود مهارت الزامی «{$skill}»";
        }
        $exp = $breakdown['experience'];
        if (($exp['score'] ?? 0) < 50) {
            $shortfall = max(0, ($exp['required_years'] ?? 0) - ($exp['total_years'] ?? 0));
            $items[] = "کمبود " . round($shortfall, 1) . " سال سابقه مرتبط";
        }
        if (($breakdown['seniority']['score'] ?? 0) < 50) {
            $items[] = "سطح ارشدیت پایین‌تر از حد انتظار موقعیت";
        }
        if (($breakdown['education']['score'] ?? 0) < 50) {
            $items[] = "عدم تطابق مدرک تحصیلی با الزام موقعیت";
        }
        if (($breakdown['preferred_skills']['score'] ?? 0) < 30) {
            $items[] = "فقدان مهارت‌های ترجیحی";
        }
        return array_values(array_unique($items));
    }

    protected function keywordsFromTitle(string $title): array
    {
        $map = [
            'backend' => ['back', 'backend', 'بک', 'بک‌اند', 'سمت سرور'],
            'frontend' => ['front', 'frontend', 'فرانت', 'رابط کاربری'],
            'devops' => ['devops', 'دواپس', 'platform', 'platform engineer'],
            'data' => ['data', 'داده', 'data engineer', 'data scientist'],
            'fullstack' => ['full', 'stack', 'فول‌استک'],
            'mobile' => ['mobile', 'android', 'ios', 'موبایل'],
        ];

        $t = mb_strtolower($title);
        $out = [];
        foreach ($map as $bucket => $keys) {
            foreach ($keys as $k) {
                if (str_contains($t, $k)) {
                    $out[] = $bucket;
                    break;
                }
            }
        }
        return $out;
    }

    protected function isTitleRelated(string $title, array $keywords): bool
    {
        if (empty($keywords)) {
            return true;
        }
        $t = mb_strtolower($title);
        foreach ($keywords as $kw) {
            $synonyms = [
                'backend'   => ['back', 'backend', 'بک', 'بک‌اند', 'php', 'laravel', 'node', 'python', 'java'],
                'frontend'  => ['front', 'frontend', 'فرانت', 'vue', 'react', 'angular', 'nuxt', 'next'],
                'devops'    => ['devops', 'دواپس', 'sre', 'platform', 'cloud', 'infrastructure'],
                'data'      => ['data', 'داده', 'analytics', 'etl', 'warehouse', 'spark', 'airflow'],
                'fullstack' => ['full', 'stack', 'فول', 'fullstack'],
                'mobile'    => ['mobile', 'android', 'ios', 'flutter', 'react native', 'موبایل'],
            ];
            foreach ($synonyms[$kw] ?? [] as $syn) {
                if (str_contains($t, $syn)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function extractDegreeRank(string $required): int
    {
        $required = mb_strtolower($required);
        if (str_contains($required, 'دکتری') || str_contains($required, 'phd')) {
            return 5;
        }
        if (str_contains($required, 'ارشد') || str_contains($required, 'master')) {
            return 4;
        }
        if (str_contains($required, 'کارشناسی') || str_contains($required, 'لیسانس') || str_contains($required, 'bachelor')) {
            return 3;
        }
        if (str_contains($required, 'کاردانی') || str_contains($required, 'associate')) {
            return 2;
        }
        if (str_contains($required, 'دیپلم')) {
            return 1;
        }
        return 0;
    }
}