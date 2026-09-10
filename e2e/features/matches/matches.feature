@matches
Feature: Matching Operations

  Background:
    Given the mock API server is ready
    And I am authenticated for candidates with mock credentials

  @run
  Scenario: Can run matching for candidates
    When I send a POST request to "/api/match/run" with body:
      """
      {
        "candidate_ids": ["candidate-1"],
        "job_position_id": "job-position-1"
      }
      """
    Then the response status should be 200
    And the response should contain "data" as an array

  @results
  Scenario: Can list match results
    When I send a GET request to "/api/match/results"
    Then the response status should be 200
    And the response should contain "data" as an array

  @show
  Scenario: Can view a specific match result
    Given I created a match result with id "123"
    When I send a GET request to "/api/match/results/123"
    Then the response status should be 200
    And the response should contain "totalScore"

  @status
  Scenario: Can update match result status
    Given I created a match result with id "123"
    When I send a PATCH request to "/api/match/results/123/status" with body:
      """
      {"status": "shortlisted"}
      """
    Then the response status should be 200
    And the response should contain "status" with value "shortlisted"