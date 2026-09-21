<?php

namespace Tests\Unit;

use App\Models\Candidate;
use App\Models\Experience;
use App\Models\JobPosition;
use App\Models\Skill;
use App\Services\Matching\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_matching_produces_deterministic_score(): void
    {
        $php = Skill::create(['name' => 'PHP', 'normalized_name' => 'php', 'category' => 'Backend', 'is_active' => true]);
        $laravel = Skill::create(['name' => 'Laravel', 'normalized_name' => 'laravel', 'category' => 'Backend', 'is_active' => true]);
        $docker = Skill::create(['name' => 'Docker', 'normalized_name' => 'docker', 'category' => 'DevOps', 'is_active' => true]);
        $redis = Skill::create(['name' => 'Redis', 'normalized_name' => 'redis', 'category' => 'Database']);

        $candidate = Candidate::create(['name' => 'تست', 'email' => 'test@test.com']);
        $candidate->experiences()->create([
            'company' => 'X', 'job_title' => 'Senior Backend Developer',
            'start_date' => now()->subYears(6), 'is_current' => true,
            'confidence' => 0.9, 'source' => 'resume',
        ]);
        $candidate->skills()->attach($php->id, ['years_experience' => 6, 'confidence' => 0.9]);
        $candidate->skills()->attach($laravel->id, ['years_experience' => 5, 'confidence' => 0.9]);
        $candidate->skills()->attach($docker->id, ['years_experience' => 3, 'confidence' => 0.9]);

        $position = JobPosition::create([
            'title' => 'Senior Backend Developer',
            'department' => 'Engineering',
            'level' => 'senior',
            'employment_type' => 'full_time',
            'min_experience_years' => 5,
            'education_requirements' => 'کارشناسی',
            'description' => 'x',
            'status' => 'open',
            'required_skills' => [
                ['name' => 'PHP', 'weight' => 9, 'min_years' => 5],
                ['name' => 'Laravel', 'weight' => 9, 'min_years' => 4],
                ['name' => 'Docker', 'weight' => 6, 'min_years' => 2],
            ],
            'preferred_skills' => ['Redis'],
        ]);

        $candidate = $candidate->fresh();
        $service = app(MatchingService::class);
        $result = $service->run($candidate, $position);

        $this->assertGreaterThan(70, $result->total_score);
        $this->assertContains('PHP', $result->breakdown['required_skills']['matched']);
        $this->assertArrayHasKey('required_skills', $result->breakdown);
        $this->assertNotEmpty($result->strengths);
    }

    public function test_matching_is_deterministic(): void
    {
        $php = Skill::create(['name' => 'PHP', 'normalized_name' => 'php']);

        $candidate = Candidate::create(['name' => 'A', 'email' => 'a@a.com']);
        $candidate->skills()->attach($php->id, ['years_experience' => 3, 'confidence' => 0.9]);

        $position = JobPosition::create([
            'title' => 'T', 'department' => 'D', 'level' => 'mid',
            'employment_type' => 'full_time', 'min_experience_years' => 0,
            'status' => 'open', 'required_skills' => [
                ['name' => 'PHP', 'weight' => 5, 'min_years' => 0],
            ],
        ]);

        $service = app(MatchingService::class);
        $r1 = $service->run($candidate, $position);
        $r2 = $service->run($candidate, $position);

        $this->assertEquals($r1->total_score, $r2->total_score);
    }
}