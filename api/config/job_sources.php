<?php

return [

    'jobvision' => [
        'name' => 'JobVision',
        'fields' => [
            'username' => [
                'type' => 'string',
                'required' => true,
                'label' => 'Username',
            ],
            'password' => [
                'type' => 'password',
                'required' => true,
                'label' => 'Password',
            ],
            'is_active' => [
                'type' => 'boolean',
                'nullable' => true,
                'default' => true,
                'label' => 'Active',
            ],
            // Additional JobVision-specific fields
            'api_url' => [
                'type' => 'string',
                'required' => false,
                'default' => 'https://employerapi.jobvision.ir',
                'label' => 'API URL',
            ],
            'account_url' => [
                'type' => 'string',
                'required' => false,
                'default' => 'https://account.jobvision.ir',
                'label' => 'Account URL',
            ],
            'captcha' => [
                'type' => 'string',
                'required' => false,
                'label' => 'CAPTCHA Token',
            ],
            'cookie' => [
                'type' => 'string',
                'required' => false,
                'label' => 'Session Cookie',
            ],
        ],
    ],

    // Mock provider — used for testing and local development.
    'mock' => [
        'name' => 'Mock',
        'fields' => [
            'is_active' => [
                'type' => 'boolean',
                'nullable' => true,
                'default' => true,
                'label' => 'Active',
            ],
        ],
    ],

    // External API provider — proxy to an external TalentMatch-compatible API.
    'external' => [
        'name' => 'External API',
        'fields' => [
            'api_url' => [
                'type' => 'string',
                'required' => true,
                'label' => 'API Base URL',
            ],
            'api_token' => [
                'type' => 'string',
                'required' => true,
                'label' => 'API Token',
            ],
            'is_active' => [
                'type' => 'boolean',
                'nullable' => true,
                'default' => true,
                'label' => 'Active',
            ],
        ],
    ],

    // LinkedIn placeholder — will be filled when LinkedIn crawler is added.
    'linkedin' => [
        'name' => 'LinkedIn',
        'fields' => [
            'username' => [
                'type' => 'string',
                'required' => true,
                'label' => 'Username/Email',
            ],
            'password' => [
                'type' => 'password',
                'required' => true,
                'label' => 'Password',
            ],
            'is_active' => [
                'type' => 'boolean',
                'nullable' => true,
                'default' => true,
                'label' => 'Active',
            ],
            // LinkedIn-specific fields
            'access_token' => [
                'type' => 'string',
                'required' => false,
                'label' => 'Access Token',
            ],
            'company_id' => [
                'type' => 'string',
                'required' => false,
                'label' => 'Company ID (for filtering)',
            ],
            'sector_ids' => [
                'type' => 'array',
                'required' => false,
                'default' => [],
                'label' => 'Sector IDs',
            ],
        ],
    ],

    // JobInja placeholder — will be filled when JobInja crawler is added.
    'jobinja' => [
        'name' => 'JobInja',
        'fields' => [
            'username' => [
                'type' => 'string',
                'required' => true,
                'label' => 'Username/Email',
            ],
            'password' => [
                'type' => 'password',
                'required' => true,
                'label' => 'Password',
            ],
            'is_active' => [
                'type' => 'boolean',
                'nullable' => true,
                'default' => true,
                'label' => 'Active',
            ],
            // JobInja-specific fields
            'api_token' => [
                'type' => 'string',
                'required' => false,
                'label' => 'API Token',
            ],
        ],
    ],

];
