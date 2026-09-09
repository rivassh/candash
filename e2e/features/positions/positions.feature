@positions
Feature: Job Positions CRUD

  Background:
    Given the API is reachable at "http://172.26.0.1:8085"
    And I am authenticated as "admin@talentmatch.local" with password "admin123"

  @list
  Scenario: Can list job positions
    When I send a GET request to "/api/JobPositions"
    Then the response status should be 200
    And the response should contain "data" as an array

  @create
  Scenario: Can create a new job position
    When I send a POST request to "/api/JobPositions" with body:
      """
      {
        "title": "E2E Test Position",
        "department": "Engineering",
        "level": "mid",
        "employment_type": "full_time",
        "min_experience_years": 2,
        "education_requirements": "کارشناسی",
        "description": "تست E2E",
        "required_skills": [{"name": "PHP", "weight": 7, "min_years": 2}]
      }
      """
    Then the response status should be 201
    And the response should contain "id"
    And the response should contain "title" with value "E2E Test Position"

  @create-validation
  Scenario: Create fails when required fields are missing
    When I send a POST request to "/api/JobPositions" with body:
      """
      {"title": ""}
      """
    Then the response status should be 422

  @show
  Scenario: Can view a specific job position
    Given I created a job position with title "Show Test Position"
    When I send a GET request to "/api/JobPositions/{id}"
    Then the response status should be 200
    And the response should contain "title" with value "Show Test Position"