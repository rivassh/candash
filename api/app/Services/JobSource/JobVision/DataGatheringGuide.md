# JobVision API Data Gathering Guide

## Overview

This guide documents the API data gathering approach for collecting candidate resumes and job applications from the JobVision platform. Based on analysis of both cchat and candash projects, here are the key patterns and solutions for effective API data collection.

## Projects Analyzed

### 1. cchat.adlr.ir (JobVision Collection Only)
- **Purpose**: Collects job post data from JobVision API
- **Data Collected**: Job titles, status, cities, application counts
- **Limitation**: Does NOT collect candidate resumes or applications
- **Endpoint**: `POST /api/jobvision/collect`
- **Missing**: No candidate/application data

### 2. candash/candash (Complete Solution)
- **Purpose**: Full candidate + job + resume data collection
- **Data Collected**:
  - Job positions (titles, departments, levels, skills)
  - Candidate applications (names, emails, phones, status, job associations)
  - Candidate resumes (raw text, parsed data, file paths)
  - Application details (resume text, full application data)
- **Key Components**:
  - `JobVisionCrawler` service for API interactions
  - `JobVisionRawPayload` table for storing raw API responses
  - `JobVisionTokenProvider` for authentication
  - Multiple API endpoints for different data types

## API Endpoints

### Job Post Collection
- **Endpoint**: `GET /api/v1.0/JobPost/GetListOfJobPosts`
- **Parameters**:
  - `statusId` (-1 for all)
  - `keyword` (search term)
  - `pageNumber` (1-based)
  - `pageSize` (default 50)
- **Returns**: List of job post summaries with IDs

### Application Collection
- **Endpoint**: `GET /api/v1.0/JobPost/GetListOfApplicationsSummary`
- **Parameters**:
  - `jobPostId` (filter by specific job)
  - `listOfApplicationsId` (specific application IDs)
- **Returns**: Application headers (candidate name, contact info, status)

- **Endpoint**: `GET /api/v1.0/JobPostApplication/GetApplicationDetails2`
- **Parameters**:
  - `jobPostId` (filter by job)
  - `applicationId` (specific application)
- **Returns**: Full application details including resume text

### Badge/Filter Data
- **Endpoint**: `GET /api/v1.0/JobPost/GetListOfJobPostBadges`
- **Parameters**:
  - `jobPostId` (filter by job)
- **Returns**: Badge information for filtering jobs

## Data Model

### Job Position
- `id` (external_id)
- `title`
- `department`
- `level`
- `employment_type`
- `required_skills`
- `preferred_skills`
- `external_id`

### Candidate
- `id`
- `name`
- `email`
- `phone`
- `linkedin_url`
- `status`
- `summary`

### Resume
- `candidate_id` (foreign key to Candidate)
- `file_path`
- `raw_text` (longText)
- `parsed_data` (jsonb)
- `status`

### Application
- `job_post_id` (foreign key to JobPost)
- `application_id` (unique per application)
- `candidate_name`
- `email`
- `phone`
- `status`
- `submitted_at`

## Data Flow

1. **Fetch Job Posts** → `GetListOfJobPosts`
2. **For each job**, fetch applications → `GetFilteredApplicationsIds` + `GetListOfApplicationsSummary`
3. **For each application**, fetch details → `GetApplicationDetails2` (includes resume text)
4. **Store raw responses** → `jobvision_raw_payloads` table
5. **Process and link** → Connect candidates ↔ applications ↔ jobs

## Key Solutions

### 1. Robust Authentication
- JobVisionCrawler implements token-based auth with retry logic
- Handles 401 errors by refreshing tokens
- Uses both bearer tokens and cookie-based auth

### 2. Paginated Data Retrieval
- Job posts fetched in pages (default 50 per page)
- Applications fetched in batches (50 per batch)
- Proper logging at each stage for monitoring

### 3. Raw Payload Preservation
- All API responses stored in `jobvision_raw_payloads` table
- Preserves original data for audit and reprocessing
- Entity types distinguish between job posts, application headers, and application details

### 4. Error Handling
- Retry mechanism for 401 (invalid token) errors
- Graceful degradation when no job posts found
- Detailed logging for troubleshooting

## Best Practices

1. **Batch Processing**: Process applications in batches to manage rate limits
2. **Rate Limiting**: Respect API limits (pageSize=50, reasonable delays)
3. **Error Recovery**: Handle 401 by refreshing tokens automatically
4. **Data Validation**: Validate candidate/resume data before storing
5. **Monitoring**: Log all API calls with URL, parameters, and response status

## Files to Modify

- `JobVisionCrawler.php` - Main crawling logic
- `JobVisionRawPayload.php` - Store raw API responses
- `JobVisionTokenProvider.php` - Manage authentication
- `JobVisionCollectionService.php` - Orchestrate collection flows
- `JobVisionController.php` - API endpoints for exposing data

## Testing Approach

1. **Unit Tests**: Mock API responses for unit testing
2. **Integration Tests**: Run full crawler against real JobVision API
3. **Data Validation**: Verify resume text is extracted correctly
4. **Performance**: Monitor API rate limits and adjust batching

## Summary

The candash project has a complete solution for gathering candidate resumes with job data from JobVision. The key insight is that cchat only collects job posts, while candash extends this to collect full candidate applications with resumes. The data flow is:

Job Posts → Applications → Resumes → Raw Payloads (storage)

This enables building a talent matching system that connects candidates to jobs with their full application data.
