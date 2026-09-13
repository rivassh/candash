@candidates
Feature: Candidates CRUD Operations

  Background:
    Given the mock API server is ready
    And I am authenticated for candidates with mock credentials

  @list
  Scenario: Can list all candidates
    When I send a GET request to "/api/candidates"
    Then the response status should be 200
    And the response should contain "data" as an array

  @create
  Scenario: Can create a new candidate
    When I send a POST request to "/api/candidates" with body:
      """
      {
        "name": "New Candidate",
        "email": "new@example.com",
        "phone": "+123456789",
        "status": "active",
        "summary": "Full Stack Developer",
        "skills": [{"name": "TypeScript", "category": "frontend"}],
        "experiences": [{"company": "Tech Corp", "jobTitle": "Developer"}]
      }
      """
    Then the response status should be 201
    And the response should contain "id"
    And the response should contain "name" with value "New Candidate"

  @create-validation
  Scenario: Create fails when required fields are missing
    When I send a POST request to "/api/candidates" with body:
      """
      {"name": ""}
      """
    Then the response status should be 422

  @search
  Scenario: Can search candidates by name
    When I send a GET request to "/api/candidates?search=Ahmed"
    Then the response status should be 200

  @show
  Scenario: Can view a specific candidate
    Given I created a candidate with name "Show Test Candidate"
    When I send a GET request to "/api/candidates/{id}"
    Then the response status should be 200
    And the response should contain "name" with value "Show Test Candidate"

  @update
  Scenario: Can update a candidate profile
    Given I created a candidate with name "Update Test Candidate"
    When I send a PUT request to "/api/candidates/{id}/profile" with body:
      """
      {
        "name": "Updated Candidate",
        "summary": "Updated summary"
      }
      """
    Then the response status should be 200
    And the response should contain "name" with value "Updated Candidate"

  @delete
  Scenario: Can delete a candidate
    Given I created a candidate with name "Delete Test Candidate"
    When I send a DELETE request to "/api/candidates/{id}"
    Then the response status should be 204