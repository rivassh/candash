<?php

namespace App\Services\Matching;

use App\Models\Skill;
use Illuminate\Support\Collection;

/**
 * نرمال‌سازی و تطبیق مهارت‌ها.
 *
 * این کلاس با استفاده از دیکشنری مهارت‌ها و مترادف‌ها، اسامی مختلف یک مهارت را به یکدیگر مرتبط می‌کند.
 */
class SkillMatcher
{
    protected array $skillIndex = [];

    public function __construct()
    {
        $this->loadIndex();
    }

    protected function loadIndex(): void
    {
        Skill::where('is_active', true)->get(['id', 'name', 'normalized_name', 'aliases'])
            ->each(function (Skill $skill) {
                $this->skillIndex[$skill->normalized_name] = $skill->id;
                foreach ($skill->aliases ?? [] as $alias) {
                    $this->skillIndex[mb_strtolower(trim($alias))] = $skill->id;
                }
            });
    }

    /**
     * پیدا کردن شناسه مهارت بر اساس نام یا یکی از مترادف‌ها.
     */
    public function resolveSkillId(string $name): ?int
    {
        $normalized = mb_strtolower(trim($name));
        return $this->skillIndex[$normalized] ?? null;
    }

    /**
     * محاسبه امتیاز تطابق مهارت کاندیدا با مهارت مورد نیاز موقعیت.
     * خروجی عددی بین ۰ تا ۱ است.
     *
     * @param float $candidateYears سال تجربه کاندیدا در مهارت
     * @param int   $requiredWeight وزن مهارت در موقعیت (۱ تا ۱۰)
     * @param int   $minYears حداقل سال لازم
     */
    public function scoreSkill(float $candidateYears, int $requiredWeight = 5, int $minYears = 0): float
    {
        if ($requiredWeight <= 0) {
            return 1.0;
        }

        // اگر کاندیدا اصلاً مهارت را ندارد → ۰
        if ($candidateYears <= 0) {
            return 0.0;
        }

        // تطابق کامل اگر به حداقل سال رسیده باشد
        if ($minYears > 0 && $candidateYears >= $minYears) {
            return 1.0;
        }

        // تطابق جزئی متناسب با نسبت سال‌ها
        if ($minYears > 0) {
            return min(1.0, $candidateYears / $minYears);
        }

        // بدون نیاز به سال مشخص، فقط وجود مهارت کافیست ولی هرچه بیشتر بهتر
        return min(1.0, 0.6 + ($candidateYears / 20));
    }
}