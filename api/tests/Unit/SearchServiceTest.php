<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Search\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = new SearchService();
    }

    public function test_can_create_search_service()
    {
        $this->assertInstanceOf(SearchService::class, $this->searchService);
    }

    public function test_index_candidate_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'indexCandidate'));
    }

    public function test_index_job_position_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'indexJobPosition'));
    }

    public function test_reindex_candidates_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'reindexCandidates'));
    }

    public function test_reindex_job_positions_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'reindexJobPositions'));
    }

    public function test_search_candidates_returns_array_structure()
    {
        $result = $this->searchService->searchCandidates('test');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('hits', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsInt($result['total']);
    }

    public function test_search_job_positions_returns_array_structure()
    {
        $result = $this->searchService->searchJobPositions('test');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('hits', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsInt($result['total']);
    }

    public function test_get_candidate_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'getCandidate'));
    }

    public function test_search_candidates_with_matching_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'searchCandidatesWithMatching'));
    }

    public function test_get_client_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'getClient'));
    }

    public function test_candidates_index_name_returns_string()
    {
        $name = $this->searchService->candidatesIndexName();
        $this->assertIsString($name);
    }

    public function test_job_positions_index_name_returns_string()
    {
        $name = $this->searchService->jobPositionsIndexName();
        $this->assertIsString($name);
    }

    public function test_get_candidates_index_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'getCandidatesIndex'));
    }

    public function test_get_job_positions_index_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'getJobPositionsIndex'));
    }

    public function test_create_candidates_index_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'createCandidatesIndex'));
    }

    public function test_create_job_positions_index_method_exists()
    {
        $this->assertTrue(method_exists($this->searchService, 'createJobPositionsIndex'));
    }
}
