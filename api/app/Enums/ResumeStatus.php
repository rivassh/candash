<?php

namespace App\Enums;

enum ResumeStatus: string
{
    case Uploaded = 'uploaded';
    case Parsed = 'parsed';
    case Enriched = 'enriched';
    case Failed = 'failed';
}