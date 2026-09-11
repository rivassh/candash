# TalentMatch — Architecture Design Document

## Overview
An intelligent talent analysis and management system with client-based architecture using Laravel backend, Nuxt frontend, and PostgreSQL/pgvector database. The system implements a transparent, formula-based matching engine for candidate-job matching.

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture Layers](#architecture-layers)
3. [Matching Engine Details](#matching-engine-details)
4. [Data Flow & Communication](#data-flow--communication)
5. [Technology Stack](#technology-stack)
6. [Design Patterns & Principles](#design-patterns--principles)
7. [Configuration & Extensibility](#configuration--extensibility)
8. [Deployment Architecture](#deployment-architecture)
9. [Testing Strategy](#testing-strategy)

## System Overview
TalentMatch is a client-based talent management system designed to analyze and match candidate profiles with job positions using a deterministic, explainable scoring formula. The architecture separates concerns into distinct layers with clear interfaces and dependency injection for testability and extensibility.

## Architecture Layers

### 1. Presentation Layer (Frontend)
**Location:** `/frontend/`
**Technology:** Nuxt 3 + Vue 3 + Tailwind CSS (RTL-first design)

**Key Components:**
- `pages/` - Route-based components (login, dashboard, candidates, positions, matches, skills, audit)
- `layouts/` - Shared layout structures
- `middleware/` - Authentication guards
- `stores/` - Pinia state management (auth)
- `composables/` - Reusable composition API functions (useApi)

**Features:**
- Persian (RTL) interface
- Real-time matching visualization
- Interactive skill dictionary
- Audit trail tracking

### 2. Application Layer (API - Laravel)
**Location:** `/api/`
**Technology:** Laravel 11 + PHP 8.4 + Sanctum Authentication

**Key Components:**
- `app/Contracts/` - Domain interfaces (dependency contracts)
- `app/Services/` - Core business logic implementations
- `app/Models/` - Eloquent ORM models
- `app/Http/Controllers/` - RESTful API endpoints
- `app/DTOs/` - Data transfer objects
- `app/Enums/` - Type-safe enumerations
- `config/talentmatch.php` - Application configuration
- `database/` - Migrations and seeders

**Services Architecture:**
- **Matching Service:** Formula-based candidate-job matching
- **JobSource Services:** Pluggable data source drivers
- **Resume Services:** Text parsing and extraction
- **Enrichment Services:** AI-powered data enhancement

### 3. Data Layer (PostgreSQL + pgvector)
**Technology:** PostgreSQL 16 with pgvector extension

**Schema Components:**
- **Relational Tables:** Users, Candidates, JobPositions, Skills, Experiences, Educations
- **Vector Storage:** pgvector for semantic similarity matching
- **JSONB Storage:** Complex matching results (breakdown, strengths, gaps)

**Relationships:**
- Candidates have many Skills, Experiences, Educations
- JobPositions reference required/preferred Skills
- MatchResults link Candidates to JobPositions with scoring data

### 4. External Services Layer
**Components:**
- **JobSource Drivers:** Mock, External API, JobVision implementations
- **Mock Services:** For development and testing (resume extraction, enrichment)
- **Redis:** Caching layer and job queuing
- **External AI Services:** Optional integration points

## Matching Engine Details

### Core Formula
The matching algorithm implements a transparent, weighted formula:

```
total_score = 0.40 × required_skills
            + 0.20 × experience
            + 0.15 × seniority
            + 0.10 × education
            + 0.10 × preferred_skills
            + 0.05 × stability
```

### Component Breakdown

#### 1. Required Skills (40%)
- **Implementation:** `scoreRequiredSkills()` in MatchingService.php
- **Logic:** Weighted matching based on skill importance and minimum experience
- **Scoring:** 0-100% based on matched skills vs requirements
- **Details:** Individual skill scores, matched/missing skill lists

#### 2. Experience (20%)
- **Implementation:** `scoreExperience()` in MatchingService.php
- **Logic:** Combined relevant experience + job title relevance
- **Scoring:** 60% ratio-based (actual vs required years) + 40% title matching
- **Details:** Total years, relevant years, experience breakdown by position

#### 3. Seniority (15%)
- **Implementation:** `scoreSeniority()` in MatchingService.php
- **Logic:** Candidate experience vs seniority level requirements
- **Scoring:** Based on years vs expected minimum for job level
- **Details:** Candidate years, job level, expected minimum

#### 4. Education (10%)
- **Implementation:** `scoreEducation()` in MatchingService.php
- **Logic:** Degree rank comparison (diploma=1 to PhD=5)
- **Scoring:** Percentage of required education level achieved
- **Details:** Best degree rank, required rank, education details

#### 5. Preferred Skills (10%)
- **Implementation:** `scorePreferredSkills()` in MatchingService.php
- **Logic:** Simple presence/absence matching
- **Scoring:** Percentage of preferred skills possessed
- **Details:** Matched preferred skills, total preferred skills

#### 6. Stability (5%)
- **Implementation:** `scoreStability()` in MatchingService.php
- **Logic:** Long-term employment detection (>2 years per position)
- **Scoring:** Ratio of long-tenured positions to total positions
- **Details:** Count of long tenures, total positions

### Output Structure
```json
{
    "total_score": 85.50,
    "breakdown": {
        "required_skills": {"score": 92.5, "details": [...], "matched": [...], "missing": [...]},
        "experience": {"score": 88.0, "total_years": 4.5, "relevant_years": 4.0},
        "seniority": {"score": 75.0, "candidate_years": 4.5, "job_level": "mid"},
        "education": {"score": 100.0, "best_degree_rank": 4},
        "preferred_skills": {"score": 60.0, "matched": [...], "total": 5},
        "stability": {"score": 100.0, "long_tenures": 2, "total": 2}
    },
    "strengths": ["تسلط بر «Java» مطابق با نیازمندی موقعیت"],
    "gaps": ["نبود مهارت الزامی «Spring Boot»"]
}
```

### Supporting Components

#### SkillMatcher Service
**Location:** `app/Services/Matching/SkillMatcher.php`
**Purpose:** Skill normalization and alias resolution
- **Index Building:** Loads all active skills with normalized names and aliases
- **Resolution:** Maps skill variations to canonical skill IDs
- **Scoring:** Computes skill match scores based on experience years

#### Configuration
**Location:** `config/talentmatch.php`
```php
'matching' => [
    'weights' => [
        'required_skills' => 40,
        'experience' => 20,
        'seniority' => 15,
        'education' => 10,
        'preferred_skills' => 10,
        'stability' => 5,
    ],
    'seniority_levels' => [
        'junior' => ['min_years' => 0,  'max_years' => 2],
        'mid'    => ['min_years' => 2,  'max_years' => 5],
        'senior' => ['min_years' => 5,  'max_years' => 100],
    ],
],
```

## Data Flow & Communication

### Request-Response Flow
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   API Gateway   │    │   Database      │
│   (Nuxt 3)      │───▶│   (Laravel)     │───▶│   (PostgreSQL)  │
│   (3000)        │    │   (8080)         │    │   + pgvector    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ Mock JobSource  │    │ Redis Cache     │    │   External      │
│   (Node.js)     │───▶│   [Queue/Cache] │───▶│   AI Services   │
│   (4000)        │    │                 │    │   (if enabled)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### API Endpoints
- **Authentication:** `POST /api/auth/login`, `POST /api/auth/logout`
- **Candidates:** `GET/POST /api/candidates`, `GET/PUT/DELETE /api/candidates/{id}`
- **Job Positions:** `GET/POST /api/positions`, `GET/PUT/DELETE /api/positions/{id}`
- **Matching:** `POST /api/matches` (run matching for candidate-position pair)
- **Skills:** `GET /api/skills` (skill dictionary)
- **Audit:** `GET /api/audit` (change tracking)

### Data Persistence Patterns
1. **Relational Data:** Candidate profiles, job postings, relationships
2. **Vector Data:** pgvector for semantic skill/experience matching (future enhancement)
3. **JSONB Storage:** Complex matching results for flexibility
4. **Caching:** Redis for frequently accessed data (skill dictionary, recent matches)

## Technology Stack

| Layer | Technology | Version | Purpose |
|-------|------------|---------|---------|
| **Frontend** | Nuxt 3 | Latest | UI Framework + SSR |
|  | Vue 3 | 3.x | Reactive Components |
|  | Tailwind CSS | Latest | Utility-first Styling |
| **Backend** | Laravel | 11.x | PHP Application Framework |
|  | PHP | 8.4 | Server-side Language |
|  | Sanctum | Latest | API Authentication |
|  | Eloquent ORM | Built-in | Database Abstraction |
| **Database** | PostgreSQL | 16 | Primary Data Store |
|  | pgvector | Latest | Vector Similarity Extension |
| **Caching/Queue** | Redis | Latest | In-memory Storage |
| **Container** | Docker Compose | - | Environment Orchestration |
| **Testing** | PHPUnit | Latest | Backend Testing |
|  | Jest/Vitest | - | Frontend Testing (planned) |

## Design Patterns & Principles

### 1. Dependency Injection
- **Implementation:** Laravel Service Container
- **Usage:** Interfaces bound to implementations in DomainServiceProvider
- **Benefits:** Testability, loose coupling, runtime flexibility

### 2. Strategy Pattern
- **Application:** JobSource drivers (Mock, External, JobVision)
- **Benefit:** Swappable data sources without changing business logic
- **Configuration:** `JOBSOURCE_DRIVER` environment variable

### 3. Repository Pattern (via Eloquent)
- **Implementation:** Model classes with relationship methods
- **Benefit:** Abstracted data access, consistent querying

### 4. Data Transfer Objects (DTOs)
- **Location:** `app/DTOs/`
- **Purpose:** Decouple internal models from external API contracts
- **Benefit:** API evolution without breaking internal code

### 5. Service Layer
- **Location:** `app/Services/`
- **Purpose:** Encapsulate business logic separate from controllers
- **Benefit:** Reusable logic, cleaner controllers, easier testing

### 6. Open/Closed Principle
- **Implementation:** Interfaces for extensibility
- **Example:** Adding new JobSource drivers requires only implementing interface
- **Benefit:** System extendable without modifying core

## Configuration & Extensibility

### Environment Configuration
**Primary:** `.env` file with Docker Compose integration
**Key Variables:**
```env
# Application
APP_NAME=TalentMatch
APP_ENV=local
APP_DEBUG=true

# Ports
API_PORT=8080
FRONTEND_PORT=3000
MOCK_PORT=4000
DB_PORT=5432

# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_DATABASE=talentmatch
DB_USERNAME=talentmatch
DB_PASSWORD=talentmatch_secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Services
JOBSOURCE_DRIVER=mock
AI_DRIVER=mock
RESUME_EXTRACTOR_DRIVER=mock
ENRICHMENT_DRIVER=mock
```

### Laravel Configuration
**File:** `config/talentmatch.php`
**Sections:**
- `client`: JobSource driver configuration
- `jobvision`: JobVision.ai integration settings
- `ai`: AI service drivers (mock by default)
- `matching`: Algorithm weights and seniority levels

### Extensibility Points
1. **New Job Sources:** Implement `JobSourceInterface`
2. **New AI Services:** Implement `EnrichmentInterface` or `ResumeExtractorInterface`
3. **Matching Formula:** Adjust weights in config/talentmatch.php
4. **Seniority Levels:** Modify seniority_levels configuration
5. **Additional Data:** Extend Eloquent models and migrations

## Deployment Architecture

### Development Environment
```
docker-compose.yml (via Makefile)
├── api:8080      # Laravel application
├── frontend:3000 # Nuxt.js frontend
├── redis:6379    # Caching and queuing
├── db:5432       # PostgreSQL + pgvector
└── mock-jobsource:4000  # Node.js job simulator
```

### Production Considerations
1. **Horizontal Scaling:**
   - Stateless frontend (CDN cacheable)
   - Laravel API servers behind load balancer
   - Redis cluster for shared caching
   - PostgreSQL read replicas for reporting

2. **Performance Optimizations:**
   - Laravel Octane for high-performance PHP
   - Redis caching for frequent queries
   - Database indexing on foreign keys and JSONB paths
   - Asset optimization and minification

3. **Monitoring & Logging:**
   - Laravel logging channels (stack driver)
   - Health check endpoints
   - Performance monitoring (Laravel Telescope or external)
   - Error tracking (Sentry or similar)

4. **Security:**
   - HTTPS termination at load balancer
   - CORS policies configured
   - Rate limiting on API endpoints
   - Input validation and sanitization
   - CSRF protection for state-changing operations

## Testing Strategy

### Unit Testing
- **Location:** `tests/Unit/`
- **Focus:** Isolated business logic testing
- **Examples:** 
  - MatchingServiceTest.php
  - SkillMatcherTest.php
  - MockDriverTest.php
- **Framework:** PHPUnit with Mockery for mocking

### Feature Testing
- **Location:** `tests/Feature/`
- **Focus:** End-to-end API workflows
- **Examples:**
  - Authentication flows
  - CRUD operations for candidates/positions
  - Matching engine integration
- **Database:** SQLite in-memory for fast tests

### Integration Testing (Planned)
- **Focus:** Cross-service interactions
- **Scenarios:**
  - Frontend to API communication
  - External service integration
  - Redis caching effectiveness
- **Environment:** Docker Compose test suite

### Test Configuration
**File:** `phpunit.xml` (to be created per PROBLEMS.md)
**Settings:**
- Test database configuration
- Code coverage requirements
- Test suite organization
- Mock framework configuration

## Architectural Decisions & Rationale

### 1. Mock-First Architecture
**Decision:** All external services have mock implementations by default
**Rationale:** 
- Enables offline development and testing
- Ensures predictable, reproducible behavior
- Reduces external dependencies during development
**Trade-off:** Requires maintaining mock implementations alongside real ones

### 2. Formula-Based Matching Engine
**Decision:** Transparent, weighted scoring formula instead of ML black box
**Rationale:**
- Explainable results for stakeholders and auditors
- Regulatory compliance in hiring contexts
- Easy to adjust and tune based on feedback
- Deterministic outputs for testing
**Trade-off:** May miss complex patterns that ML could capture

### 3. pgvector Integration
**Decision:** PostgreSQL with pgvector extension for semantic matching
**Rationale:**
- Leverages existing relational database investment
- Provides advanced similarity search capabilities
- Maintains ACID properties for transactional safety
- Avoids introducing separate vector database
**Trade-off:** Requires learning pgvector-specific querying

### 4. Separation of Concerns (Laravel + Nuxt)
**Decision:** Clear API boundary between frontend and backend
**Rationale:**
- Enables independent development and deployment
- Supports multiple client types (web, mobile, third-party)
- Facilitates API-first development approach
- Clear contract definition through API specification
**Trade-off:** Requires handling authentication and state synchronization

### 5. Comprehensive Interface Contracts
**Decision:** Repository and service interfaces for all external dependencies
**Rationale:**
- Enables dependency injection and mocking
- Facilitates test-driven development
- Allows implementation swapping without changing consumers
- Documents expected behavior through method signatures
**Trade-off:** Increased number of files and initial setup complexity

## Future Enhancements

### Short-term (0-3 months)
1. Complete PHPUnit test suite implementation
2. Add API documentation (OpenAPI/Swagger)
3. Implement frontend tests with Vitest/Jest
4. Add pagination and filtering to list endpoints
5. Enhance error handling and validation responses

### Medium-term (3-6 months)
1. Implement real pgvector-based semantic matching
2. Add webhook support for external integrations
3. Introduce caching layers for expensive operations
4. Add role-based access control enhancements
5. Implement batch processing for large matching jobs

### Long-term (6+ months)
1. Machine learning enhancements to matching formula
2. Multi-tenant architecture support
3. Advanced analytics and reporting dashboard
4. Mobile application development (React Native)
5. Internationalization (i18n) beyond Persian/English

## Conclusion
The TalentMatch architecture provides a solid foundation for a talent management system with a focus on transparency, testability, and extensibility. The modular design allows for gradual enhancement while maintaining a reliable core system. The combination of Laravel's robust backend features with Nuxt's modern frontend capabilities creates a productive development environment suitable for both rapid iteration and enterprise-scale deployment.