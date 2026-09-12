<?php

namespace Tests\Unit;

use App\Services\JobSource\JobVision\JobVisionTokenProvider;
use Tests\TestCase;

class JobVisionTokenProviderTest extends TestCase
{
    public function test_token_provider_rejects_expired_token(): void
    {
        $provider = new JobVisionTokenProvider();

        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode(['exp' => time() - 100, 'iat' => time() - 200, 'client_id' => 'EmployerClient']));
        $sig = base64_encode('fake');
        $expiredToken = "$header.$payload.$sig";

        $this->assertFalse($provider->isValid($expiredToken));
    }

    public function test_token_provider_rejects_invalid_segment_count(): void
    {
        $twoSegment = "header.payload";
        $provider = new JobVisionTokenProvider();

        $this->assertFalse($provider->isValid($twoSegment));
    }

    public function test_token_provider_rejects_malformed_payload(): void
    {
        $provider = new JobVisionTokenProvider();

        // Payload with invalid base64 that produces malformed JSON
        $header = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9';
        $malformedPayload = 'eyJ4IjogIjEyMzQ1Njc4OTAiLCJ5IjogIjs9PSf9.invalid_signature';

        $this->assertFalse($provider->isValid("$header.$malformedPayload.invalid_signature"));
    }

    public function test_token_provider_accepts_valid_token(): void
    {
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => 'https://account.jobvision.ir',
            'nbf' => time(),
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => ['JobVisionApi', 'IdentityServerApi'],
            'scope' => ['openid', 'profile', 'JobVisionApi', 'roles', 'offline_access', 'IdentityServerApi'],
            'amr' => ['pwd'],
            'client_id' => 'EmployerClient',
            'sub' => '2318',
            'auth_time' => time(),
            'idp' => 'local',
            'email' => 'sharifinv@mapsa.com',
            'http://schemas.microsoft.com/ws/2008/06/identity/claims/role' => 'Employer',
            'HashLastLvidId' => 'f27331256aaf685580bb081d7c55740c533464a685cc98da2accc74983182',
            'Role' => 'Employer',
            'CompanyID' => '69945',
            'CompanyName' => 'Yalķin-2',
            'CompanyEnName' => 'Mapsa',
            'IsCompanyActive' => '1',
            'EmailConfirmed' => '1',
            'FullName' => 'Yalķin-2',
            'IsProfileComplete' => '1',
            'IsOwnerProfileComplete' => '1',
            'IsAdmin' => '1',
            'IsAdminOperator' => '1',
            'IsNewCompany' => '0',
            'IsNewOperator' => '1',
            'FCP' => '0',
            'FL' => '0',
            'sid' => '9A283BA43DE1E1123CA15DA8C2E8',
            'jti' => 'C6600646243D0BC810A6F5A5463AD9',
        ]));
        $sig = base64_encode('fake-signature');
        $validToken = "$header.$payload.$sig";

        $provider = new JobVisionTokenProvider();

        $this->assertTrue($provider->isValid($validToken));
    }
}