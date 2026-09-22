<?php

namespace Tests\Unit\Services\JobSource\JobVision;

use App\DTOs\JobSource\ApplicationDetailsDto;
use App\DTOs\JobSource\ApplicationHeaderDto;
use App\DTOs\JobSource\ApplicationSummaryDto;
use App\DTOs\JobSource\CandidateDto;
use App\DTOs\JobSource\PositionDto;
use App\Services\JobSource\JobVision\JobVisionResponseNormalizer;

use PHPUnit\Framework\TestCase;

class JobVisionNormalizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testToPositionDtoConvertsSampleJobPost(): void
    {
        $jobPost = [
            'jobPostId' => 'EXT-1001',
            'title' => 'Senior Backend Developer',
            'department' => 'Engineering',
            'seniorityLevelId' => '3',
            'employmentTypeId' => '1',
            'minExperienceYears' => 5,
            'description' => 'Lead backend development',
            'requiredSkills' => [
                ['name' => 'PHP', 'weight' => 9, 'minYears' => 5],
                ['name' => 'Laravel', 'weight' => 8, 'minYears' => 4]
            ],
            'preferredSkills' => ['AWS', 'Docker']
        ];

        $dto = JobVisionResponseNormalizer::toPositionDto($jobPost);

        $this->assertInstanceOf(PositionDto::class, $dto);
        $this->assertEquals('EXT-1001', $dto->externalId);
        $this->assertEquals('Senior Backend Developer', $dto->title);
        $this->assertEquals('Engineering', $dto->department);
        $this->assertEquals('senior', $dto->level);
        $this->assertEquals('full_time', $dto->employmentType);
        $this->assertEquals(5, $dto->minExperienceYears);
        $this->assertEquals('Lead backend development', $dto->description);
        $this->assertEquals([
            ['name' => 'PHP', 'weight' => 9, 'min_years' => 5],
            ['name' => 'Laravel', 'weight' => 8, 'min_years' => 4]
        ], $dto->requiredSkills);
        $this->assertEquals(['AWS', 'Docker'], $dto->preferredSkills);
    }

    public function testToPositionDtoHandlesMissingData(): void
    {
        $jobPost = [
            'id' => 'EXT-2002',
            'jobTitle' => 'Frontend Developer',
            'category' => 'Web',
            'level' => 'mid',
            'type' => 'contract',
            'minExperience' => 2,
            'jobDescription' => 'Build UIs with Vue',
            'skills' => ['HTML', 'CSS', 'JavaScript'],
        ];

        $dto = JobVisionResponseNormalizer::toPositionDto($jobPost);

        $this->assertInstanceOf(PositionDto::class, $dto);
        $this->assertEquals('EXT-2002', $dto->externalId);
        $this->assertEquals('Frontend Developer', $dto->title);
        $this->assertEquals('Web', $dto->department);
        $this->assertEquals('mid', $dto->level);
        $this->assertEquals('contract', $dto->employmentType);
        $this->assertEquals(2, $dto->minExperienceYears);
        $this->assertEquals('Build UIs with Vue', $dto->description);
        $this->assertEquals([
            ['name' => 'HTML', 'weight' => 5, 'min_years' => 0],
            ['name' => 'CSS', 'weight' => 5, 'min_years' => 0],
            ['name' => 'JavaScript', 'weight' => 5, 'min_years' => 0]
        ], $dto->requiredSkills);
        $this->assertEmpty($dto->preferredSkills);
    }

    public function testToCandidateDtoConvertsHeader(): void
    {
        $header = [
            'applicationId' => 'APP-5001',
            'fullName' => 'علی ناصر',
            'email' => 'ali@example.com',
            'mobile' => '+989121111111'
        ];

        $dto = JobVisionResponseNormalizer::toCandidateDto($header);

        $this->assertInstanceOf(CandidateDto::class, $dto);
        $this->assertEquals('APP-5001', $dto->externalId);
        $this->assertEquals('علی ناصر', $dto->name);
        $this->assertEquals('ali@example.com', $dto->email);
        $this->assertEquals('+989121111111', $dto->phone);
    }

    public function testToApplicationSummaryDtoConvertsSummary(): void
    {
        $summary = [
            'applicationId' => 'APP-6001',
            'jobPostId' => 'JOB-7001',
            'applicationStatus' => 'pending',
            'submittedAt' => '2023-05-15 10:30:00',
            'candidateName' => 'سارا خطیبی'
        ];

        $dto = JobVisionResponseNormalizer::toApplicationSummaryDto($summary);

        $this->assertInstanceOf(ApplicationSummaryDto::class, $dto);
        $this->assertEquals('APP-6001', $dto->externalId);
        $this->assertEquals('JOB-7001', $dto->jobPostId);
        $this->assertEquals('APP-6001', $dto->applicationId);
        $this->assertEquals('pending', $dto->status);
        $this->assertEquals('2023-05-15 10:30:00', $dto->submittedAt);
        $this->assertEquals('سارا خطیبی', $dto->candidateName);
    }

    public function testToApplicationHeaderDtoConvertsHeader(): void
    {
        $header = [
            'applicationId' => 'APP-8001',
            'jobPostId' => 'JOB-9001',
            'candidateName' => 'نوید رضایی',
            'email' => 'navid@example.com',
            'mobile' => '+989122222222',
            'applicationStatus' => 'accepted',
            'submittedAt' => '2023-06-01 14:20:00'
        ];

        $dto = JobVisionResponseNormalizer::toApplicationHeaderDto($header);

        $this->assertInstanceOf(ApplicationHeaderDto::class, $dto);
        $this->assertEquals('APP-8001', $dto->externalId);
        $this->assertEquals('APP-8001', $dto->applicationId);
        $this->assertEquals('JOB-9001', $dto->jobPostId);
        $this->assertEquals('نوید رضایی', $dto->candidateName);
        $this->assertEquals('navid@example.com', $dto->email);
        $this->assertEquals('+989122222222', $dto->phone);
        $this->assertEquals('accepted', $dto->status);
        $this->assertEquals('2023-06-01 14:20:00', $dto->submittedAt);
    }

    public function testToApplicationDetailsDtoConvertsDetails(): void
    {
$details = [
            'applicationId' => 'APP-9002',
            'id' => 'DETAIL-001',
            'resumeText' => 'نویسنده: علیčki\n(ok)\n/application.php',
            'data' => ['company' => 'TestCo', 'role' => 'Developer']
        ];

        $dto = JobVisionResponseNormalizer::toApplicationDetailsDto($details);

        $this->assertInstanceOf(ApplicationDetailsDto::class, $dto);
        $this->assertEquals('APP-9002', $dto->externalId);
        $this->assertEquals('APP-9002', $dto->applicationId);
        $this->assertEquals('نویسنده: علیčki\n(ok)\n/application.php', $dto->resumeText);
        $this->assertEquals(['company' => 'TestCo', 'role' => 'Developer'], $dto->details);
    }

    public function testResumeToTextExtractsFromDetails(): void
    {
        $details = [
            'personalInfo' => ['name' => 'Test User', 'email' => 'test@example.com'],
            'workExperiences' => [['title' => 'Developer', 'company' => 'TestCorp']],
            'educations' => [['fieldOfStudy' => 'Computer Science', 'institutionName' => 'Uni']],
            'skills' => [['title' => 'PHP', 'yearsOfExperience' => 3]],
            'languages' => [['name' => 'English', 'proficiency' => 'fluent']],
            'summary' => 'Experienced developer',
            'cvText' => null,
            'rawText' => 'Full CV text from file'
        ];

        $text = JobVisionResponseNormalizer::resumeToText($details);

        $this->assertEquals('Full CV text from file', $text);
    }

    public function testResumeToTextFlattensWhenNoRawText(): void
    {
        $details = [
            'personalInfo' => ['name' => 'John Doe', 'email' => 'john@example.com'],
            'workExperiences' => [['title' => 'Developer', 'company' => 'ABC Corp']],
            'educations' => [['fieldOfStudy' => 'Software Engineering']],
            'skills' => [['title' => 'JavaScript', 'yearsOfExperience' => 2]],
            'languages' => [],
            'summary' => 'Junior developer seeking opportunities',
            'cvText' => null,
            'rawText' => null
        ];

        $text = JobVisionResponseNormalizer::resumeToText($details);

        $this->assertStringContainsString('Personal Info:', $text);
        $this->assertStringContainsString('Name: John Doe', $text);
        $this->assertStringContainsString('Email: john@example.com', $text);
        $this->assertStringContainsString('Work Experience:', $text);
        $this->assertStringContainsString('Title: Developer', $text);
        $this->assertStringContainsString('Company: ABC Corp', $text);
        $this->assertStringContainsString('Education:', $text);
        $this->assertStringContainsString('FieldOfStudy: Software Engineering', $text);
        $this->assertStringContainsString('Skills:', $text);
        $this->assertStringContainsString('JavaScript', $text);
        $this->assertStringContainsString('Summary:', $text);
        $this->assertStringContainsString('Junior developer seeking opportunities', $text);
    }
}
