@search
Feature: Meilisearch Search
  As an authenticated user, I want to search candidates and job positions through the Meilisearch-backed API

  Background:
    Given the API server is ready and database connection is stable
    And I am authenticated as "admin@talentmatch.local" with password "admin123"
    And I am authenticated for candidates with mock credentials

  @search-candidates-by-name
  Scenario: Can search candidates by name using Meilisearch
    When I send a GET request to "/api/search/candidates?q=Search"
    Then the response status should be 200
    And the response should contain "data"
    And the response should contain "meta"

  @search-jobs-by-title
  Scenario: Can search job positions by title using Meilisearch
    When I send a GET request to "/api/search/jobs?q=Engineer"
    Then the response status should be 200
    And the response should contain "data"
    And the response should contain "meta"

  @search-filters
  Scenario: Can filter candidates by status
    When I send a GET request to "/api/search/candidates?q=Filter&status=new"
    Then the response status should be 200
    And the response should contain "data"

  @search-health
  Scenario: Search health endpoint returns Meilisearch status
    When I send a GET request to "/api/search/health"
    Then the response status should be 200
    And the response should contain "meilisearch"
    And the response should contain "status"

  @search-reindex
  Scenario: Reindex endpoint returns message
    When I send a POST request to "/api/search/reindex"
    Then the response status should be 200
    And the response should contain "message"
    And the response should contain "type"

  @search-empty-results
  Scenario: Search returns empty results when no matches
    When I send a GET request to "/api/search/candidates?q=NoSuchCandidate12345"
    Then the response status should be 200
    And the response should contain "data" as an array
    And the response should contain "meta"

  @search-match-with-job
  Scenario: Match endpoint returns 404 for non-existent job position
    When I send a GET request to "/api/search/match?job_position_id=99999"
    Then the response status should be 404
    And the response should contain "message"
