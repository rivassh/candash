<?php

return [

    'jobvision' => [
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
        ],
    ],

    // LinkedIn placeholder — will be filled when LinkedIn crawler is added.
    'linkedin' => [
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
        ],
    ],

    // JobInja placeholder — will be filled when JobInja crawler is added.
    'jobinja' => [
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
        ],
    ],
];
