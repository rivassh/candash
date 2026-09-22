<?php

namespace Tests\Unit\Services\JobSource\JobVision;

class JobVisionSampleFixture
{
    public const SAMPLE = <<<'JSON'
{
  "applicationId": "102",
  "jobPostId": "1001",
  "personalInfo": {
    "fullName": "محمد رحیمی",
    "firstName": "محمد",
    "lastName": "رحیمی",
    "email": "mohammad@example.com",
    "mobile": "+989120000000"
  },
  "workExperiences": [
    {
      "title": "Senior Backend Developer",
      "companyName": "TechCorp",
      "startDate": "1399-01-01",
      "isCurrent": true,
      "description": "Laravel development",
      "confidence": 0.9,
      "source": "resume"
    },
    {
      "title": "Backend Developer",
      "companyName": "DevCo",
      "startDate": "1397-01-01",
      "endDate": "1398-12-31",
      "isCurrent": false,
      "description": "PHP development",
      "confidence": 0.8,
      "source": "resume"
    }
  ],
  "educations": [
    {
      "fieldOfStudy": "Apache Airflow",
      "degree": "Masters",
      "institutionName": "University of Science",
      "graduationYear": 1401,
      "confidence": 0.85,
      "source": "resume"
    }
  ],
  "skills": [
    {"title": "PHP", "yearsOfExperience": 5},
    {"title": "Laravel", "yearsOfExperience": 4}
  ],
  "languages": [
    {"name": "Persian", "proficiency": "native"},
    {"name": "English", "proficiency": "intermediate"}
  ],
  "summary": "Experienced backend developer with 5+ years in PHP/Laravel.",
  "cvText": null
}
JSON;
}