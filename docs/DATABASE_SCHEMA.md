# TalentMatch — Database Schema Documentation

## Overview
The TalentMatch database is built on **PostgreSQL 16** with **pgvector** extension. It uses a relational schema with JSONB columns for flexible data storage (matching breakdowns, enrichment data, audit changes). All migrations are located in `api/database/migrations/`.

## Schema Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                         SCHEMA                                          │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                         │
│  ┌──────────────┐       ┌──────────────────┐       ┌──────────────────┐                 │
│  │   users      │       │   candidates     │       │  job_positions   │                 │
│  │--------------│       │------------------│       │------------------│                 │
│  │ id (PK)      │       │ id (PK)          │       │ id (PK)          │                 │
│  │ name         │       │ name             │       │ title            │                 │
│  │ email (UNIQ) │       │ email            │       │ department       │                 │
│  │ password     │       │ phone            │       │ level (ENUM)     │                 │
│  │ role (ENUM)  │       │ linkedin_url     │       │ employment_type  │                 │
│  │ is_active    │       │ status (ENUM)    │       │ min_experience   │                 │
│  │ created_at   │◄──┐   │ summary          │       │ education_reqs   │                 │
│  │ updated_at   │   │   │ enrichment_data  │       │ description      │                 │
│  └──────────────┘   │   │ created_at       │       │ external_id (UN) │                 │
│                     │   │ updated_at       │       │ status (ENUM)    │                 │
│                     │   └───────┬──────────┘       │ required_skills  │                 │
│                     │           │ (1:N)             │ preferred_skills │                 │
│                     │           │                   │ created_at       │                 │
│                     │           │                   │ updated_at       │                 │
│                     │           │                   └────────┬─────────┘                 │
│                     │           │                            │                           │
│                     │           │                            │ (N:M via               │
│                     │           │                            │  match_results)          │
│                     │           │                            │                           │
│  ┌──────────────┐   │   ┌───────┴──────────┐                 │                           │
│  │ audit_logs   │   │   │  candidate_skill │◄───────────────┘                           │
│  │--------------│   │   │------------------│                                            │
│  │ id (PK)      │   │   │ id (PK)          │                                            │
│  │ user_id (FK) │   │   │ candidate_id (FK)│                                            │
│  │ model_type   │   │   │ skill_id (FK)    │                                            │
│  │ model_id     │   │   │ years_experience │                                            │
│  │ action       │   │   │ confidence       │                                            │
│  │ changes (JSON)│  │   │ created_at       │                                            │
│  │ ip_address   │   │   │ updated_at       │                                            │
│  │ created_at   │   │   └──────────────────┘                                            │
│  └──────────────┘   │                                                                      │
│                     │                                                                      │
│                     │ (1:N)           (1:N)        (1:N)          (1:N)                    │
│                     ├───────────────►├─────────────►├─────────────►├─────────────────┐     │
│                     │               │              │              │                  │     │
│  ┌──────────────┐   │   ┌──────────┐│  ┌───────────┐│  ┌───────────┐│  ┌───────────┐ │     │
│  │ resumes      │   │   │ skills   ││  │experiences││  │ educations││  │match_results│ │     │
│  │--------------│   │   │----------││  │-----------││  │-----------││  │-------------│ │     │
│  │ id (PK)      │   │   │ id (PK)  ││  │ id (PK)   ││  │ id (PK)   ││  │ id (PK)     │ │     │
│  │ candidate_id │───┘   │ name     ││  │candidate_id││  │candidate_id││  │candidate_id │ │     │
│  │ file_path    │       │normalized││  │ company   ││  │ degree    ││  │job_position │ │     │
│  │ raw_text     │       │ _name(UN)││  │ job_title ││  │ field     ││  │ total_score │ │     │
│  │ parsed_data  │       │ aliases  ││  │ start_date││  │ institution││  │ breakdown   │ │     │
│  │ status (ENUM)│       │ category ││  │ end_date  ││  │ grad_year ││  │ strengths   │ │     │
│  │ confidence   │       │ is_active││  │ is_current││  │ confidence││  │ gaps        │ │     │
│  │ created_at   │       └──────────┘│  │ confidence││  │ source    ││  │ status      │ │     │
│  │ updated_at   │                    │  │ source  ││  │ created_at││  │ notes       │ │     │
│  └──────────────┘                    │  │ created_at││  │ updated_at││  │ created_at  │ │     │
│                                      │  └───────────┘│  └───────────┘│  │ updated_at  │ │     │
│                                      │                 │                 └──────┬──────┘ │     │
│                                      │                 │                        │        │     │
│                                      │         ┌───────┴──────────────┐          │        │     │
│                                      │         │ job_position_skill   │◄─────────┘        │     │
│                                      │         │----------------------│                   │     │
│                                      │         │ job_position_id (FK) │                   │     │
│                                      │         │ skill_id (FK)        │                   │     │
│                                      │         └──────────────────────┘                   │     │
│                                      │                                                   │     │
│  ┌──────────────┐                                                                       │     │
│  │personal_access│                                                                      │     │
│  │_tokens       │                                                                      │     │
│  │--------------│                                                                      │     │
│  │ id (PK)      │                                                                      │     │
│  │ tokenable    │                                                                      │     │
│  │ name         │                                                                      │     │
│  │ token (UNIQ) │                                                                      │     │
│  │ abilities    │                                                                      │     │
│  │ last_used_at │                                                                      │     │
│  │ expires_at   │                                                                      │     │
│  │ created_at   │                                                                      │     │
│  └──────────────┘                                                                      │     │
│                                                                                       │     │
└─────────────────────────────────────────────────────────────────────────────────────────┘─┘
```

## Table Details

### 1. users
Authentication table for system users (admin, HR specialists). Uses Laravel Sanctum for token-based authentication.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| name | `varchar` | NOT NULL | User display name |
| email | `varchar` | NOT NULL, UNIQUE | Login email |
| password | `varchar` | NOT NULL | Hashed password |
| role | `enum` | NOT NULL, DEFAULT `hr_specialist` | `admin`, `hr_specialist` |
| is_active | `boolean` | NOT NULL, DEFAULT `true` | Account status |
| remember_token | `varchar` | NULL | Laravel remember token |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Indexes:** `email` (UNIQUE)

**Related Models:** `User.php`

---

### 2. candidates
Stores candidate profiles with optional enrichment data from AI services.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| name | `varchar` | NOT NULL | Candidate name |
| email | `varchar` | NULL | Contact email |
| phone | `varchar` | NULL | Contact phone |
| linkedin_url | `varchar` | NULL | LinkedIn profile URL |
| status | `enum` | NOT NULL, DEFAULT `new` | `new`, `active`, `inactive`, `hired`, `archived` |
| summary | `text` | NULL | Brief description |
| enrichment_data | `jsonb` | NULL | AI enrichment results |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Indexes:** `status`, `email`

**JSONB Structure** (`enrichment_data`):
```json
{
    "ai_summary": "...",
    "skills_extracted": ["skill1", "skill2"],
    "experience_summary": "..."
}
```

**Related Models:** `Candidate.php` — hasMany Resumes, Experiences, Educations, Skills (via pivot), MatchResults

---

### 3. job_positions
Stores job postings with required and preferred skills stored as JSON arrays.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| title | `varchar` | NOT NULL | Job title |
| department | `varchar` | NOT NULL | Department name |
| level | `enum` | NOT NULL | `junior`, `mid`, `senior` |
| employment_type | `enum` | NOT NULL | `full_time`, `part_time`, `contract`, `internship` |
| min_experience_years | `smallint` | NOT NULL, DEFAULT `0` | Minimum years of experience |
| education_requirements | `varchar` | NULL | Education level required |
| description | `text` | NULL | Job description |
| external_id | `varchar` | NULL, UNIQUE | External system reference |
| status | `enum` | NOT NULL, DEFAULT `draft` | `draft`, `open`, `closed`, `filled` |
| required_skills | `jsonb` | NULL | `[{"name": "Laravel", "weight": 5, "min_years": 2}, ...]` |
| preferred_skills | `jsonb` | NULL | `["skill1", "skill2", ...]` |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Indexes:** `status`, `department`

**JSONB Structure** (`required_skills`):
```json
[
    {"name": "Laravel", "weight": 5, "min_years": 2},
    {"name": "Vue.js", "weight": 3, "min_years": 1}
]
```

**Related Models:** `JobPosition.php` — belongsTo (no parent), hasMany MatchResults, belongsToMany Candidates (via match_results)

---

### 4. skills
Normalized skills dictionary with aliases for matching variations.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| name | `varchar` | NOT NULL | Display name |
| normalized_name | `varchar` | NOT NULL, UNIQUE | Lowercase trimmed name (lookup key) |
| aliases | `jsonb` | NULL | `["alias1", "alias2", ...]` |
| category | `varchar` | NULL | Skill category (e.g., `programming`, `soft_skill`) |
| is_active | `boolean` | NOT NULL, DEFAULT `true` | Active status |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Indexes:** `normalized_name` (UNIQUE), `category`

**JSONB Structure** (`aliases`):
```json
["Laravel", "php", "Laravel Framework"]
```

**Related Models:** `Skill.php` — belongsToMany Candidates (via `candidate_skill`), belongsToMany JobPositions (via `job_position_skill`)

---

### 5. resumes
Stores resume documents and parsed data per candidate.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| candidate_id | `bigint` | FK → candidates, CASCADE | Owner candidate |
| file_path | `varchar` | NULL | Stored file path |
| raw_text | `longtext` | NULL | Extracted text content |
| parsed_data | `jsonb` | NULL | Structured extraction |
| status | `enum` | NOT NULL, DEFAULT `uploaded` | `uploaded`, `processing`, `parsed`, `failed` |
| confidence | `float` | NULL | Parsing confidence (0-1) |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**JSONB Structure** (`parsed_data`):
```json
{
    "name": "...",
    "email": "...",
    "skills": ["Laravel", "Vue"],
    "summary": "..."
}
```

**Related Models:** `Resume.php` — belongsTo Candidate

---

### 6. experiences
Work history for candidates with duration calculation helpers.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| candidate_id | `bigint` | FK → candidates, CASCADE | Owner candidate |
| company | `varchar` | NOT NULL | Company name |
| job_title | `varchar` | NOT NULL | Position title |
| start_date | `date` | NOT NULL | Start date |
| end_date | `date` | NULL | End date (NULL = current) |
| is_current | `boolean` | NOT NULL, DEFAULT `false` | Current employment |
| responsibilities | `jsonb` | NULL | Array of responsibilities |
| confidence | `float` | NOT NULL, DEFAULT `0.9` | Data confidence |
| source | `varchar` | NOT NULL, DEFAULT `resume` | Data source |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Computed Methods:**
- `getDurationMonths()` — returns total months (uses `now()` if current)
- `getDurationYears()` — returns duration in years (1 decimal)

**JSONB Structure** (`responsibilities`):
```json
["Managed team of 5", "Developed API endpoints", "Deployed to production"]
```

**Related Models:** `Experience.php` — belongsTo Candidate

---

### 7. educations
Educational background for candidates with degree ranking for matching.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| candidate_id | `bigint` | FK → candidates, CASCADE | Owner candidate |
| degree | `varchar` | NOT NULL | Degree name |
| field_of_study | `varchar` | NOT NULL | Field of study |
| institution | `varchar` | NOT NULL | Institution name |
| graduation_year | `integer` | NULL | Year of graduation |
| confidence | `float` | NOT NULL, DEFAULT `0.9` | Data confidence |
| source | `varchar` | NOT NULL, DEFAULT `resume` | Data source |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Degree Ranking** (used in matching):
| Degree | Rank |
|--------|------|
| دیپلم / high_school | 1 |
| کاردانی / associate | 2 |
| کارشناسی / bachelor / لیسانس | 3 |
| کارشناسی ارشد / فوق لیسانس / master | 4 |
| دکتری / phd | 5 |

**Related Models:** `Education.php` — belongsTo Candidate

---

### 8. candidate_skill (Pivot Table)
Many-to-many relationship between candidates and skills with proficiency data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| candidate_id | `bigint` | FK → candidates, CASCADE | Candidate reference |
| skill_id | `bigint` | FK → skills, CASCADE | Skill reference |
| years_experience | `float` | NOT NULL, DEFAULT `0` | Years of experience |
| confidence | `float` | NOT NULL, DEFAULT `1.0` | Data confidence |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Indexes:** UNIQUE (`candidate_id`, `skill_id`)

**Related Models:** Candidate `skills()` → BelongsToMany Skill (with pivot)

---

### 9. match_results
Core matching results from the Matching Engine. One record per candidate-position pair.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| candidate_id | `bigint` | FK → candidates, CASCADE | Candidate reference |
| job_position_id | `bigint` | FK → job_positions, CASCADE | Job position reference |
| total_score | `float` | NOT NULL, DEFAULT `0` | Overall matching score (0-100) |
| breakdown | `jsonb` | NULL | Per-component scores |
| strengths | `jsonb` | NULL | Positive matching points |
| gaps | `jsonb` | NULL | Negative matching points |
| status | `enum` | NOT NULL, DEFAULT `pending` | `pending`, `approved`, `rejected` |
| notes | `text` | NULL | Reviewer comments |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

**Indexes:** UNIQUE (`candidate_id`, `job_position_id`), `status`, `total_score`

**JSONB Structure** (`breakdown`):
```json
{
    "required_skills": {"score": 92.5, "details": [], "matched": ["Laravel"], "missing": ["Docker"]},
    "experience": {"score": 88.0, "total_years": 4.5, "relevant_years": 4.0, "details": []},
    "seniority": {"score": 75.0, "candidate_years": 4.5, "job_level": "mid", "expected_min_years": 2},
    "education": {"score": 100.0, "best_degree_rank": 4, "required_rank": 4},
    "preferred_skills": {"score": 60.0, "matched": ["Vue"], "total": 5},
    "stability": {"score": 100.0, "long_tenures": 2, "total": 2}
}
```

**JSONB Structure** (`strengths`):
```json
["تسلط بر «Laravel» مطابق با نیازمندی موقعیت", "سابقه کاری مرتبط (4.5 سال)"]
```

**JSONB Structure** (`gaps`):
```json
["نبود مهارت الزامی «Docker»", "کمبود 0.5 سال سابقه مرتبط"]
```

**Related Models:** `MatchResult.php` — belongsTo Candidate, belongsTo JobPosition

---

### 10. audit_logs
Immutable audit trail tracking all changes to system entities.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| user_id | `bigint` | FK → users, NULL (SET NULL on delete) | Perpetrator (nullable) |
| model_type | `varchar` | NOT NULL | Polymorphic type (e.g., `App\Models\Candidate`) |
| model_id | `bigint` | NOT NULL | Polymorphic ID |
| action | `varchar` | NOT NULL | Action (`created`, `updated`, `deleted`, `status_changed`) |
| changes | `jsonb` | NULL | Field-level changes |
| ip_address | `varchar(45)` | NULL | Client IP |
| created_at | `timestamp` | NOT NULL | Event timestamp |

**Indexes:** (`model_type`, `model_id`), `user_id`

**JSONB Structure** (`changes`):
```json
{
    "old": {"status": "new", "name": "John"},
    "new": {"status": "active", "name": "John Doe"}
}
```

**Important:** `timestamps = false` (no `updated_at` column, only `created_at`)

---

### 11. personal_access_tokens (Sanctum)
Laravel Sanctum token storage for API authentication.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| tokenable_type | `varchar` | NOT NULL | Polymorphic type |
| tokenable_id | `bigint` | NOT NULL | Polymorphic ID |
| name | `varchar` | NOT NULL | Token label |
| token | `varchar(64)` | NOT NULL, UNIQUE | Hashed token value |
| abilities | `text` | NULL | JSON array of permissions |
| last_used_at | `timestamp` | NULL | Last usage timestamp |
| expires_at | `timestamp` | NULL | Expiration timestamp |
| created_at | `timestamp` | NOT NULL | Creation timestamp |
| updated_at | `timestamp` | NOT NULL | Update timestamp |

---

### 12. job_position_skill (Pivot Table)
Associates required skills with job positions (complementary to JSONB in job_positions).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | `bigint` | PK, auto-increment | Primary key |
| job_position_id | `bigint` | FK → job_positions, CASCADE | Job reference |
| skill_id | `bigint` | FK → skills, CASCADE | Skill reference |

**Indexes:** None (simple relationship table)

---

## Relationship Summary

### One-to-Many (1:N)
| Parent | Child | Foreign Key | Cascade |
|--------|-------|-------------|---------|
| candidates | resumes | candidate_id | Yes |
| candidates | experiences | candidate_id | Yes |
| candidates | educations | candidate_id | Yes |
| candidates | match_results | candidate_id | Yes |
| job_positions | match_results | job_position_id | Yes |
| users | audit_logs | user_id | No (null on delete) |

### Many-to-Many (N:M)
| Table 1 | Pivot | Table 2 | Pivot Columns |
|---------|-------|---------|---------------|
| candidates | candidate_skill | skills | years_experience, confidence |
| job_positions | job_position_skill | skills | — |

### Polymorphic
| Parent | Related | Through |
|--------|---------|---------|
| audit_logs | candidates, job_positions, skills, etc. | model_type + model_id |
| personal_access_tokens | users | tokenable_type + tokenable_id |

## Migration Order

| Order | Migration | Table |
|-------|-----------|-------|
| 1 | `0001_01_01_000000_create_users_table` | users, personal_access_tokens |
| 2 | `2024_01_01_000001_create_job_positions_table` | job_positions |
| 3 | `2024_01_01_000002_create_candidates_table` | candidates |
| 4 | `2024_01_01_000003_create_resumes_table` | resumes |
| 5 | `2024_01_01_000004_create_skills_table` | skills |
| 6 | `2024_01_01_000005_create_experiences_table` | experiences |
| 7 | `2024_01_01_000006_create_educations_table` | educations |
| 8 | `2024_01_01_000007_create_match_results_table` | match_results |
| 9 | `2024_01_01_000008_create_candidate_skill_table` | candidate_skill |
| 10 | `2024_01_01_000009_create_audit_logs_table` | audit_logs |
| 11 | `2024_01_02_000010_create_jobvision_raw_payloads_table` | jobvision_raw_payloads |

## PostgreSQL Extensions Required

```sql
-- pgvector for vector similarity search (future enhancement)
CREATE EXTENSION IF NOT EXISTS vector;
```

## Seed Data

Database seeders (`api/database/seeders/`) are used to populate:
- Admin and HR test users (per README: `admin@talentmatch.local` / `admin123`, `hr@talentmatch.local` / `hr123456`)
- Default skills with normalized names and aliases
- Sample candidates, job positions, and match results
