<?php

namespace App\Contracts\Enrichment;

interface EnrichmentInterface
{
    /**
     * غنی‌سازی پروفایل کاندیدا از منبع خارجی (مثل LinkedIn).
     *
     * @return array enriched data
     */
    public function enrichByLinkedin(string $linkedinUrl, ?string $name = null): array;

    public function driverName(): string;
}