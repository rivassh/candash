<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
    }

    public function test_search_health_returns_ok(): void
    {
        $response = $this->getJson('/api/search/health');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
            'meilisearch' => 'available',
        ]);
    }

    public function test_search_candidates_returns_paginated(): void
    {
        $response = $this->getJson('/api/search/candidates');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => ['total', 'page', 'per_page', 'last_page']
        ]);
    }

    public function test_search_jobs_returns_paginated(): void
    {
        $response = $this->getJson('/api/search/jobs');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => ['total', 'page', 'per_page', 'last_page']
        ]);
    }

    public function test_search_candidates_accepts_query(): void
    {
        $response = $this->getJson('/api/search/candidates?q=test');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_reindex_returns_message(): void
    {
        $response = $this->postJson('/api/search/reindex');
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'type']);
    }

    public function test_match_returns_404_for_missing_job(): void
    {
        $response = $this->getJson('/api/search/match?job_position_id=99999');
        $response->assertStatus(404);
        $response->assertJson(['message' => 'Job position not found.']);
    }
}
