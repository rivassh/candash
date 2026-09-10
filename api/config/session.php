<?php

return [
    'default' => env('SESSION_DRIVER', 'redis'),
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'talentmatch_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN', 'localhost'),
    'secure' => null,
    'http_only' => true,
    'same_site' => 'lax',
];