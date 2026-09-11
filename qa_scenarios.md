# QA Scenarios for TalentMatch

## Overview

This document defines manual test scenarios for the TalentMatch system (Project: candash). These scenarios cover functional, integration, edge-case, and performance testing areas.

## 1. Matching Engine Functionality

### Scenario 1.1: Basic Candidate-Match Score Calculation
- **Description**: Verify that the matching service correctly calculates total scores based on the formula: `total = 0.40 × required_skills + 0.20 × experience + 0.15 × seniority + 0.10 × education + 0.10 × preferred_skills + 0.05 × stability`
- **Steps**:
  1. Create a candidate with specific skill counts, years of experience, seniority level, education level, and stability rating.
  2. Execute the matching service with predefined parameters.
  3. Validate that the output `total_score` falls within expected range (0-100).
  4. Breakdown should contain individual component scores.
- **Expected Result**: Total score calculated correctly according to the weighted formula.

### Scenario 1.2: Edge Case - Zero Values
- **Description**: Test scoring when some factors are zero (e.g., no required skills, no experience).
- **Steps**:
  1. Create a candidate with zero values for certain attributes.
  2. Run matching service.
  3. Verify no division-by-zero or invalid calculation errors occur.
- **Expected Result**: Valid score computed; zero values treated appropriately.

### Scenario 1.3: Weight Validation
- **Description**: Ensure weights sum to approximately 1.0 (0.40+0.20+0.15+0.10+0.10+0.05 = 1.0).
- **Steps**:
  1. Inspect the matching formula constants in `api/app/Services/Matching/MatchingService.php`.
  2. Verify each weight matches the documented values.
- **Expected Result**: Weights sum to 1.0 (within floating-point tolerance).

## 2. API Endpoint Testing

### Scenario 2.1: POST /api/matches
- **Description**: Create a new match request and verify successful creation.
- **Steps**:
  1. Send a POST request to `http://localhost:8080/api/matches` with valid candidate data.
  2. Check response status (201 Created).
  3. Verify the match object is stored in the database.
- **Expected Result**: Match created successfully with correct fields populated.

### Scenario 2.2: GET /api/matches/{id}
- **Description**: Retrieve a specific match by ID.
- **Steps**:
  1. Obtain a match ID from the previous step.
  2. Request the match via GET endpoint.
  3. Validate the returned data matches the stored match.
- **Expected Result**: Correct match data retrieved.

### Scenario 2.3: Error Handling - Invalid Input
- **Description**: Test API resilience with invalid/missing parameters.
- **Steps**:
  1. Send a POST request with missing required fields.
  2. Observe error response (400 Bad Request).
  3. Verify appropriate error messages are returned.
- **Expected Result**: Proper validation errors with descriptive messages.

## 3. Frontend-Backend Coordination

### Scenario 3.1: Real-time Match Updates
- **Description**: Verify frontend displays matches dynamically as new data arrives.
- **Steps**:
  1. Start the frontend (`http://localhost:3000`).
  2. Trigger a match creation via backend API.
  3. Observe frontend UI updating with new match.
- **Expected Result**: New match appears in the dashboard within seconds.

### Scenario 3.2: Authentication Flow
- **Description**: Test that unauthorized access is properly restricted.
- **Steps**:
  1. Attempt to access `/api/matches` without authentication.
  2. Verify 401 Unauthorized response.
  3. Log in with admin credentials (`admin@talentmatch.local` / `admin123`).
  4. Access the same endpoint and verify success.
- **Expected Result**: Unauthenticated requests rejected; authenticated requests succeed.

## 4. Integration & Data Consistency

### Scenario 4.1: Database Transaction Integrity
- **Description**: Ensure matches are consistently stored across related records.
- **Steps**:
  1. Create a candidate and initiate a match.
  2. Verify the candidate's profile and the match record are linked correctly.
  3. Rollback the transaction (if supported) and verify no orphaned records remain.
- **Expected Result**: Data consistency maintained; no orphaned records.

### Scenario 4.2: Concurrent Match Creation
- **Description**: Test behavior under simultaneous match creation requests.
- **Steps**:
  1. Simulate multiple concurrent POST requests to create matches.
  2. Verify all matches are created and assigned unique IDs.
- **Expected Result**: No duplicate matches; all requests processed correctly.

## 5. Edge Cases & Error Handling

### Scenario 5.1: Network Partition / Service Unavailable
- **Description**: Test system behavior when the mock job source is unavailable.
- **Steps**:
  1. Stop the mock-jobsource service temporarily.
  2. Attempt to create a match requiring job enrichment.
  3. Observe graceful degradation or error handling.
- **Expected Result**: System handles the failure gracefully (e.g., returns 503 or falls back to cached data).

### Scenario 5.2: Large Dataset Performance
- **Description**: Test performance with a large number of candidates.
- **Steps**:
  1. Seed the database with hundreds of candidate records.
  2. Run the matching service for a subset of candidates.
  3. Measure response time and resource usage.
- **Expected Result**: Acceptable response time (< 5 seconds for typical queries).

## 6. Security & Compliance

### Scenario 6.1: Input Sanitization
- **Description**: Verify that user inputs are sanitized to prevent injection attacks.
- **Steps**:
  1. Submit a candidate with special characters, SQL-like strings, or HTML in skill descriptions.
  2. Check that the data is properly escaped/stored.
- **Expected Result**: Malicious input is neutralized; no XSS or SQL injection occurs.

### Scenario 6.2: Role-Based Access Control
- **Description**: Ensure only authorized roles can access certain endpoints.
- **Steps**:
  1. Attempt to access HR-specific endpoints as a regular user.
  2. Verify access denied.
- **Expected Result**: Role-based permissions enforced.

## 7. Local Development Environment

### Scenario 7.1: Environment Configuration
- **Description**: Validate that the local environment is properly configured.
- **Steps**:
  1. Check `.env` file for correct settings (DB_PORT, API_PORT, FRONTEND_PORT).
  2. Verify mock-jobsource is running on port 4000.
  3. Confirm API is accessible on port 8080.
- **Expected Result**: All services start correctly with correct configurations.

### Scenario 7.2: Seeding Data
- **Description**: Ensure the database is properly seeded for testing.
- **Steps**:
  1. Run `make seed` to populate the database.
  2. Query the database to confirm sample data exists.
- **Expected Result**: Sample candidates and jobs are present in the database.

## 8. Regression & Smoke Tests

### Scenario 8.1: Full System Smoke Test
- **Description**: Verify core functionality works end-to-end.
- **Steps**:
  1. Login as HR user.
  2. Create a candidate.
  3. Generate a match.
  4. View the match in the dashboard.
- **Expected Result**: Complete flow succeeds without errors.

### Scenario 8.2: API Health Check
- **Description**: Confirm all critical endpoints respond correctly.
- **Steps**:
  1. Hit each major endpoint (/api/matches, /api/users, /api/jobs).
  2. Verify 200 OK responses.
- **Expected Result**: All health checks pass.

## Test Data Sets

| ID | Description | Purpose |
|----|-------------|---------|
| TC-01 | Basic match with standard values | Validate core scoring logic |
| TC-02 | Zero-value edge case | Test weight handling |
| TC-03 | High-skill candidate | Verify premium scoring |
| TC-04 | Missing required fields | Test validation |
| TC-05 | Concurrent matches | Test race condition handling |
| TC-06 | Large dataset query | Performance test |
| TC-07 | Unauthorized access | Security test |
| TC-08 | Invalid input submission | Security test |

## Test Execution Guide

1. **Prerequisites**: Start all services (`make up`), seed the database (`make seed`).
2. **Run Matches**: Use the matching service endpoints to create test matches.
3. **Verify Responses**: Check both API responses and frontend updates.
4. **Document Findings**: Record any discrepancies in the QA log.
5. **Cleanup**: Stop services after testing.

## Owner
- **QA Lead**: TalentMatch Team
- **Reviewer**: Engineering Team
- **Frequency**: Daily smoke tests, weekly regression tests

---
*Last Updated: 2026-09-11*
