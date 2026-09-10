<?php

namespace App\Contracts\JobSource;

use App\DTOs\JobSource\PositionDto;
use App\DTOs\JobSource\CandidateDto;

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
     * نام/شناسه درایور فعلی
     */
    public function driverName(): string;
}