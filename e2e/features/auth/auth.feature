@auth
Feature: Authentication Flow

  Background:
    Given the API is reachable at "http://172.26.0.1:8085"

  @login
  Scenario: Admin can login with valid credentials
    When I send a POST request to "/api/auth/login" with:
      | email          | password     |
      | admin@talentmatch.local | admin123 |
    Then the response status should be 200
    And the response should contain "token"
    And the response should contain a user object with "name" and "role"

  @login-fail
  Scenario: Login fails with wrong password
    When I send a POST request to "/api/auth/login" with:
      | email          | password |
      | admin@talentmatch.local | wrongpassword |
    Then the response status should be 401

  @me
  Scenario: Authenticated user can fetch their profile
    Given I am authenticated as "admin@talentmatch.local" with password "admin123"
    When I send a GET request to "/api/auth/me"
    Then the response status should be 200
    And the response should contain "name" and "email"

  @logout
  Scenario: Authenticated user can logout
    Given I am authenticated as "admin@talentmatch.local" with password "admin123"
    When I send a POST request to "/api/auth/logout"
    Then the response status should be 200