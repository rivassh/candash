<?php

namespace Tests\Unit;

use App\Services\JobSource\JobVision\JobVisionTokenProvider;
use Tests\TestCase;

class JobVisionTokenProviderTest extends TestCase
{
    public function test_token_provider_rejects_expired_token(): void
    {
        $provider = new JobVisionTokenProvider(
            'https://employerapi.jobvision.ir',
            'https://account.jobvision.ir',
            null,
            null,
            null,
            null
        );

        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode(['exp' => time() - 100, 'iat' => time() - 200, 'client_id' => 'EmployerClient']));
        $sig = base64_encode('fake');
        $expiredToken = "$header.$payload.$sig";

        $this->assertFalse($provider->isValid($expiredToken));
    }

    public function test_token_provider_rejects_invalid_segment_count(): void
    {
        $twoSegment = "header.payload";
        $provider = new JobVisionTokenProvider(
            'https://employerapi.jobvision.ir',
            'https://account.jobvision.ir',
            null,
            null,
            null,
            null
        );

        $this->assertFalse($provider->isValid($twoSegment));
    }

    public function test_token_provider_rejects_malformed_payload(): void
    {
        $provider = new JobVisionTokenProvider(
            'https://employerapi.jobvision.ir',
            'https://account.jobvision.ir',
            null,
            null,
            null,
            null
        );

        // Payload with invalid base64 that produces malformed JSON
        $header = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9';
        $malformedPayload = 'eyJ4IjogIjEyMzQ1Njc4OTAiLCJ5IjogIjs9PSf9.invalid_signature';

        $this->assertFalse($provider->isValid("$header.$malformedPayload.invalid_signature"));
    }

    public function test_token_provider_accepts_valid_token(): void
    {
        $validToken = "eyJhbGciOiJSUzI1NiIsImtpZCI6IkZEMjczNDJEQjU4RTFFREZEMzJBODQ4MUVGODU0REUwQzI5Q0I3MzdSUzI1NiIsIng1dCI6Il9TYzBMYldPSHRfVEtvU0I3NFZONE1LY3R6YyIsInR5cCI6ImF0K2p3dCJ9.eyJpc3MiOiJodHRwczovL2FjY291bnQuam9idmlzaW9uLmlyIiwibmJmIjoxNzg5MTE0MjQ5LCJpYXQiOjE3ODkxMTQyNDksImV4cCI6MTc4OTEzNTg0OSwiYXVkIjpbIkpvYlZpc2lvbkFwaSIsIklkZW50aXR5U2VydmVyQXBpIl0sInNjb3BlIjpbIm9wZW5pZCIsInByb2ZpbGUiLCJKb2JWaXNpb25BcGkiLCJyb2xlcyIsIklkZW50aXR5U2VydmVyQXBpIiwib2ZmbGluZV9hY2Nlc3MiXSwiYW1yIjpbInB3ZCJdLCJjbGllbnRfaWQiOiJFbXBsb3llckNsaWVudCIsInN1YiI6IjIzMTgiLCJhdXRoX3RpbWUiOjE3ODkwNDUxOTgsImlkcCI6ImxvY2FsIiwiZW1haWwiOiJzaGFyaWZpbnZAbWFwc2FlbmcuY29tIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiRW1wbG95ZXIiLCJIYXNoZWRMaXZlQ2hhdElkIjoiZjI3MzMxMjU2YWFmNjg1NTgwYmIzYWIwODFkN2M1NTc0MGM1MzM0NjRhNjg1Y2M1OGRiMWFjY2M3NDk4MzE4MiIsIlJvbGUiOiJFbXBsb3llciIsIkNvbXBhbnlJRCI6IjY5OTQ1IiwiQ29tcGFueU5hbWUiOiLZhdm-2LXYpyIsIkNvbXBhbnlFbk5hbWUiOiJNYXBzYSIsIklzQ29tcGFueUFjdGl2YXRlZCI6IjEiLCJFbWFpbENvbmZpcm1lZCI6IjEiLCJGdWxsTmFtZSI6ItmG2YjbjNivINi02LHbjNmB24wiLCJJc1Byb2ZpbGVDb21wbGV0ZWQiOiIxIiwiSXNPcGVyYXRvclByb2ZpbGVDb21wbGV0ZWQiOiIxIiwiSXNBZG1pbiI6IjEiLCJJc0FkbWluT3BlcmF0b3IiOiIxIiwiSXNOZXdDb21wYW55IjoiMCIsIklzTmV3T3BlcmF0b3IiOiIxIiwiRkNQIjoiMCIsIkZMIjoiMCIsInNpZCI6IjlBMjgzQkE0MUREMEUxREUxMjgzQ0ExNUREQThDMkU4IiwianRpIjoiQzY2MDA2NDYyNDNEMUJDODEwNUE2RjVGQTU0NjNBNDkifQ.TiDKEGsOvAA4imi3CjHYNdUTkC0wFntR3HxWPzN5FAZrWZZocJntWm_RwO4kgEekk-gByRe47_cPoTtGhATlAabgmXPWcx1sqCjCVXO1EKw4fwjDyv6J5ryrHVEJs0osE5XeIDPRC1kXjbb8Xc6o-H1h5OXH5YZXb4f8mDUOZsY2vI0KB3B6FbEjTve848Ar_Act0w74x7vRvCOuRB7pLJa6XrYvd2C9ghAcDRnXcL1ul2pIYn53uGcWs4hpOvG1BlTyrPQIGZDnH7_u_naff0nv78hM8Ve_jwjMkzEzfT2yv9QDH686QpWr-76WdHm8J-iY3_IfzwpnwmVKT7e6_g";

        $provider = new JobVisionTokenProvider(
            'https://employerapi.jobvision.ir',
            'https://account.jobvision.ir',
            null,
            null,
            null,
            null
        );

        $this->assertTrue($provider->isValid($validToken));
    }
}