<?php

return [
    'guards' => ['web' => ['driver' => 'session'], 'sanctum' => ['driver' => 'sanctum']],
    'expiration' => null,
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
    'middleware' => ['authenticate_session' => \Illuminate\Session\Middleware\AuthenticateSession::class],
];