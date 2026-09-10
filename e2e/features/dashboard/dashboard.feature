@dashboard
Feature: Dashboard

  Background:
    Given the mock API server is ready
    And I am authenticated for candidates with mock credentials

  @summary
  Scenario: Dashboard summary returns valid structure
    When I send a GET request to "/api/dashboard/summary"
    Then the response status should be 200

  @candidates
  Scenario: Dashboard summary contains candidates count
    When I send a GET request to "/api/dashboard/summary"
    Then the response should contain "candidates"

  @positions
  Scenario: Dashboard summary contains positions count
    When I send a GET request to "/api/dashboard/summary"
    Then the response should contain "positions"

  @matches
  Scenario: Dashboard summary contains matches count
    When I send a GET request to "/api/dashboard/summary"
    Then the response should contain "matches"