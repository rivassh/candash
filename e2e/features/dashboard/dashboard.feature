@dashboard
Feature: Dashboard

  Background:
    Given the API is reachable at "http://172.26.0.1:8085"
    And I am authenticated as "admin@talentmatch.local" with password "admin123"

  @summary
  Scenario: Dashboard summary returns valid structure
    When I send a GET request to "/api/dashboard/summary"
    Then the response status should be 200
    And the response should contain "candidates" with nested fields "total", "new", "in_review"
    And the response should contain "positions" with nested fields "total", "open"
    And the response should contain "matches" with nested fields "total", "shortlisted"
    And the response should contain "top_matches" as an array