# QA Scenarios for TalentMatch (candash)

> Version: v2.0.0 | Updated: 2026-09-18
> API base: `http://localhost:8082/api` | Frontend: `http://localhost:3080/search`
> Auth: `admin@talentmatch.local` / `admin123` (Sanctum stateful)

## 0. Prerequisites

```
docker compose -f docker-compose.yml up -d          # all 5 services
docker compose -f docker-compose.yml exec api vendor/bin/phpunit   # 32 tests
```

## 1. Authentication & Auth

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| AUTH-01 | Login success | POST `/api/auth/login` with valid creds | 200 + Sanctum token |
| AUTH-02 | Login failure | POST with wrong password | 401 |
| AUTH-03 | Auth me | GET `/api/auth/me` with Bearer token | 200 + user |
| AUTH-04 | Logout | POST `/api/auth/logout` | 200 + token revoked |

## 2. Job Position Management

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| POS-01 | List positions | GET `/api/JobPositions` | 200 + array |
| POS-02 | Create position | POST with title/required_skills/preferred_skills | 201 + stored JSONB |
| POS-03 | Import from JobVision | POST `/api/job-positions/import-from-source` | 118 positions imported |
| POS-04 | Get curl command | GET `/api/job-positions/import-curl` | 200 + curl string |

## 3. Matching Engine (core)

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| MAT-01 | Deterministic score | POST `/api/match/run` with candidate+position | total_score > 70 for good match |
| MAT-02 | Idempotent re-run | Call run twice with same inputs | scores equal |
| MAT-03 | Required skills missing | Candidate lacks a required skill | score dropped per weight |
| MAT-04 | Zero required skills | Position has empty `required_skills` | score 100 for that component |
| MAT-05 | Preferred skills not present | Candidate lacks preferred skill | score 0 for that component |
| MAT-06 | Breakdown structure | Inspect `breakdown` keys | required_skills, experience, seniority, education, preferred_skills, stability |
| MAT-07 | Strengths/gaps arrays | Inspect `strengths` and `gaps` | non-empty when applicable |

## 4. Candidates

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| CAN-01 | List candidates | GET `/api/candidates` | 200 |
| CAN-02 | Upload resume | POST `/api/candidates/{id}/resume` | 200 |
| CAN-03 | Enrich LinkedIn | POST `/api/candidates/{id}/enrich-linkedin` | 200 |
| CAN-04 | Update profile | PUT `/api/candidates/{id}/profile` | 200 |

## 5. Skills Dictionary

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| SKL-01 | List skills | GET `/api/skills` | 200 |
| SKL-02 | CRUD skills | POST/PUT/DELETE | 201/200/204 |

## 6. Search (Meilisearch)

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| SRC-01 | Search candidates | GET `/api/search/candidates?q=...` | 200 + results |
| SRC-02 | Search jobs | GET `/api/search/jobs?q=...` | 200 + results |
| SRC-03 | Search health | GET `/api/search/health` | 200 + ok |
| SRC-04 | Reindex | POST `/api/search/reindex` | 200 |
| SRC-05 | Match with search | GET `/api/search/match` | 200 |

## 7. Match Results (CRUD)

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| MCH-01 | List results | GET `/api/match/results` | 200 |
| MCH-02 | Get result | GET `/api/match/results/{id}` | 200 |
| MCH-03 | Update status | PATCH `/api/match/results/{id}/status` | 200 |

## 8. JobVision Credentials (Admin)

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| JV-01 | List credentials | GET `/api/jobvision-credentials` | 200 |
| JV-02 | Create credential | POST with username/password | 201 |
| JV-03 | Activate | POST `/api/jobvision-credentials/{id}/activate` | 200 |

## 9. Browser-assisted JobVision Login

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| BRO-01 | Start browser login | POST `/api/admin/jobvision-browser-login` | 200 + sessionId |
| BRO-02 | Status poll | GET `/api/admin/jobvision-browser-login/status` | 200 |
| BRO-03 | Screenshot | GET `/api/admin/jobvision-browser-login/screenshot` | 200 + image |
| BRO-04 | Complete | POST `/api/admin/jobvision-browser-login/complete` | 200 |
| BRO-05 | VNC endpoint | GET `/api/admin/jobvision-browser-login/vnc` | 200 |

## 10. Audit Logs

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| AUD-01 | List logs | GET `/api/audit-logs` | 200 |

## 11. Edge Cases & Error Handling

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| EDGE-01 | Expired JobVision JWT | Use expired token | 401, no false isValid bypass |
| ELSE-02 | Captcha disabled | Attempt captcha login | omitted (permanent) |
| EDGE-03 | Missing `</template>` | Frontend build | no element missing tag error |
| EDGE-04 | Host port 8080 conflict | API port collision | use 8082 |

## 12. Performance

| ID | Scenario | Steps | Expected |
|----|----------|-------|----------|
| PERF-01 | 118 positions reindex | POST `/api/search/reindex` after import | < 30s |
| PERF-02 | Match 100 candidates | Batch run `/api/match/run` | linear scaling |

## Test Data Sets

| ID | Purpose |
|----|---------|
| TC-01 | Good match (all skills present, seniority met) |
| TC-02 | Partial match (missing 1 required skill) |
| TC-03 | Zero required skills position |
| TC-04 | No candidate experiences |
| TC-05 | Unauthorized access attempt |
| TC-06 | Invalid input (missing fields) |

## Test Execution Guide

1. Start services: `docker compose -f docker-compose.yml up -d`
2. Run unit tests inside container: `docker compose -f docker-compose.yml exec api vendor/bin/phpunit`
3. Manual API verification: use curl with Bearer token from AUTH-01
4. Frontend smoke: open `http://localhost:3080/search`
5. Record findings in QA log; any regression re-run full suite

## Known Issues (open)

- MatchingServiceTest `test_matching_produces_deterministic_score` may fail with 40.0 if SkillMatcher index loads before test data (see `SkillMatcher::loadIndex()` lazy loading fix applied; re-verify after container restart)
- Import endpoint 500 if `route('login')` missing — check `JobVisionTokenProvider::login()`
- Host port 8080 occupied by frps — API uses 8082

## Owner

- QA Lead: TalentMatch Team
- Reviewer: Engineering Team
- Frequency: Daily smoke, weekly regression
