<?php

namespace App\Contracts\JobSource;

use App\DTOs\JobSource\PositionDto;
use App\DTOs\JobSource\CandidateDto;
use App\DTOs\JobSource\ApplicationSummaryDto;
use App\DTOs\JobSource\ApplicationHeaderDto;

interface JobSourceInterface
{
    /**
     * دریافت لیست موقعیت‌های شغلی از منبع
     *
     * @return PositionDto[]
     */
    public function listPositions(): array;

    /**
     * دریافت یک موقعیت شغلی بر اساس شناسه خارجی
     */
    public function getPosition(string $externalId): ?PositionDto;

    /**
     * دریافت لیست کاندیداهای متصل
     *
     * @return CandidateDto[]
     */
    public function listCandidates(): array;

    /**
     * دریافت رزومه متنی یک کاندیدا
     */
    public function getCandidateResume(string $externalId): ?string;

    /**
     * لیست درخواست‌ها برای یک موقعیت شغلی
     *
     * @return ApplicationSummaryDto[]
     */
    public function listApplications(int $jobPostId): array;

    /**
     * جزئیات یک درخواست
     */
    public function getApplicationDetails(string $applicationId): ?array;

    /**
     * نام/شناسه درایور فعلی
     */
    public function driverName(): string;

    /**
     * فراخوانی متدهای import از طریق CLI
     */
    public function importPositions(int $page = 1, int $pageSize = 50): array;
}