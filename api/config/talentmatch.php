<?php

return [
    'client' => [
        'driver' => env('JOBSOURCE_DRIVER', 'mock'),
        'base_url' => env('JOBSOURCE_BASE_URL', 'http://mock-jobsource:4000'),
        'token' => env('JOBSOURCE_API_TOKEN'),
        'timeout' => 10,
    ],

    'jobvision' => [
        'token'       => env('JOBVISION_TOKEN'),
        'username'    => env('JOBVISION_USERNAME'),
        'password'    => env('JOBVISION_PASSWORD'),
        'captcha'     => env('JOBVISION_CAPTCHA_TOKEN'),
        'cookie'      => env('JOBVISION_COOKIE'),
        'account_url' => env('JOBVISION_ACCOUNT_URL', 'https://account.jobvision.ir'),
        'api_url'     => env('JOBVISION_API_URL', 'https://employerapi.jobvision.ir'),
    ],

    'ai' => [
        'driver' => env('AI_DRIVER', 'mock'),
        'resume_extractor' => env('RESUME_EXTRACTOR_DRIVER', 'mock'),
        'enrichment' => env('ENRICHMENT_DRIVER', 'mock'),
    ],

    'matching' => [
        'weights' => [
            'required_skills' => 40,
            'experience'       => 20,
            'seniority'        => 15,
            'education'        => 10,
            'preferred_skills' => 10,
            'stability'        => 5,
        ],
        'seniority_levels' => [
            'junior' => ['min_years' => 0,  'max_years' => 2],
            'mid'    => ['min_years' => 2,  'max_years' => 5],
            'senior' => ['min_years' => 5,  'max_years' => 100],
        ],
    ],
];