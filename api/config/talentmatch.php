<?php

return [
    'elasticsearch' => [
        'host' => env('ELASTICSEARCH_HOST', 'elasticsearch'),
        'port' => (int) env('ELASTICSEARCH_PORT', 9200),
        'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
        'username' => env('ELASTICSEARCH_USERNAME', ''),
        'password' => env('ELASTICSEARCH_PASSWORD', ''),
        'index_candidates' => env('ELASTICSEARCH_INDEX_CANDIDATES', 'candidates'),
        'index_jobs' => env('ELASTICSEARCH_INDEX_JOBS', 'job_positions'),
    ],

    'client' => [
        'driver' => env('JOBSOURCE_DRIVER', 'mock'),
        'base_url' => env('JOBSOURCE_BASE_URL', 'http://mock-jobsource:4000'),
        'token' => env('JOBSOURCE_API_TOKEN'),
        'timeout' => 10,
    ],

'jobvision' => [
        'token'       => env('JOBVISION_TOKEN'),
        'username'    => env('talentmatch.jobvision.username'),
        'password'    => env('talentmatch.jobvision.password'),
        'captcha'     => env('talentmatch.jobvision.captcha'),
        'cookie'      => env('talentmatch.jobvision.cookie'),
        'account_url' => env('JOBVISION_ACCOUNT_URL', 'https://account.jobvision.ir'),
        'api_url'     => env('JOBVISION_API_URL', 'https://employerapi.jobvision.ir'),
        'job_post_ids' => [],
    ],

    'ai' => [
        'driver' => env('AI_DRIVER', 'mock'),
        'resume_extractor' => env('RESUME_EXTRACTOR_DRIVER', 'mock'),
        'enrichment' => env('ENRICHMENT_DRIVER', 'mock'),
    ],

    'search' => [
        'driver' => env('SEARCH_DRIVER', 'meilisearch'),
        'host' => env('MEILI_HOST', 'meilisearch'),
        'port' => env('MEILI_PORT', 7700),
        'master_key' => env('MEILI_MASTER_KEY', 'local-dev-key'),
        'index_candidates' => 'candidates',
        'index_jobs' => 'job_positions',
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