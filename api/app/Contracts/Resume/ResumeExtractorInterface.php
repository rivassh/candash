<?php

namespace App\Contracts\Resume;

use App\DTOs\Resume\ParsedResumeDto;

interface ResumeExtractorInterface
{
    /**
     * استخراج ساختار یافته اطلاعات از متن یا فایل رزومه
     */
    public function extract(string $rawTextOrPath): ParsedResumeDto;
}