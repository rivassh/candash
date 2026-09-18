# Changelog

All notable changes to the Candash project are documented in this file.

## [Unreleased] - 2026-09-18

### Added

#### Token Refresh Fallback for JobVision Import
- Implemented robust token refresh mechanism in `JobVisionTokenProvider::getToken()`
- System now handles expired JWT tokens gracefully:
  - First checks config for cached token
  - Falls back to existing cache if valid
  - Triggers login() to obtain fresh token on 401
  - Retries original request with new token
  - Provides user-friendly error modals with curl commands for manual retry
- Added `importCurlCommand()` endpoint for generating manual curl commands with actual tokens
- Improved error handling in `JobVisionCrawler` with better auth failure messages

#### Frontend Improvements
- **Vue Template Syntax Fix**: Resolved "Element is missing end tag" build error in `frontend/pages/positions/index.vue`
  - Added missing `</template>` closing tag
  - Rewrote template section cleanly to fix Vue build
- **User-Friendly Error Handling**: Added error modal display in frontend
  - 401 error detection and user-friendly modal display
  - `useApi.ts` composable enhanced with error handling
  - `useJobVisionError.ts` new composable for JobVision-specific errors
- **Frontend Build**: Successfully rebuilt `candash-frontend` Docker image
  - Port remapped to 3080 (was 3000)
  - All built routes now functional
  - UI serves correctly at `http://127.0.0.1:3080/search` (HTTP 200)

#### Search Service Replacement
- **Meilisearch Integration**: Replaced Elasticsearch with Meilisearch
  - Updated `composer.json`: Added `meilisearch/meilisearch-php: ^1.0`
  - Removed `elasticsearch/elasticsearch: ^9.5`
  - Updated `SearchService.php` to use Meilisearch client (`Meilisearch\Client`)
  - All search endpoints functional: `/api/search/candidates`, `/api/search/jobs`, `/api/search/match`
  - Persian/Unicode support maintained
  - Filter syntax updated to string expressions: `"status = 'active'", "skills.skill_id IN [1,2,3]"`

#### Infrastructure & Configuration
- **Port Conflict Resolution**: Host port 8080 occupied by `frps` process
  - Remapped API port: 8080 → 8082 (`.env`, `docker-compose.yml`)
  - Remapped Frontend port: 3000 → 3080 (`.env`)
  - Fixed frontend `API_URL` in `docker-compose.yml` to use container-internal port 8000
  - All containers now running and healthy
- **Docker Compose Updates**:
  - Updated `api/Dockerfile` with PHP 8.4, extensions, and browser agent dependencies
  - Updated `e2e/docker-compose.yml` with mock API configuration
- **Nginx Proxy Configuration**: Updated to point to `candash-frontend:3000`
  - Fixed 502 error for `candash.adlr.ir`
  - All search routes working correctly

#### Admin & Monitoring
- **JobVision Browser Login**: Added VNC-based interactive browser for JobVision login modal
  - Implemented Playwright agent with WebSocket screenshot streaming
  - Cookie capture auto-save functionality
  - Admin panel for JobVision credentials management
  - `JobVisionBrowserLoginController.php` with full CRUD operations
- **Application Crawler**: Implemented JobVision application crawling flow
  - `JobVisionCrawler::crawlApplications()` method
  - Supports all 118 job posts with application data
  - Creates Candidates and Resumes via `JobSourceImporter::importCandidates()`
- **Testing**: All 7 E2E scenarios passing
  - Authentication tests
  - Candidates management tests
  - Dashboard tests
  - Matches tests
  - Positions tests
  - Search tests

#### Code Quality & Architecture
- **API Controllers**: Enhanced with new functionality
  - `JobPositionController::importFromSource()` with error modals
  - `JobApplicationController` for REST endpoints
  - `SearchController` for Meilisearch integration
- **Database**: Production DB remains read-only
  - All test data uses factories in isolated test database
  - SearchServiceProvider with Meilisearch client configuration
- **Security**: Sanctum authentication maintained
  - Bearer token required for all API endpoints
  - `Authenticate` middleware configured for API routes
- **Documentation**: Updated AGENTS.md with working rules and guidelines

### Fixed

#### Bug Fixes
- **Vue Template Syntax**: Missing `</template>` closing tag in `frontend/pages/positions/index.vue`
  - Fixed build error causing "Element is missing end tag"
  - Rewrote template section to ensure proper structure
- **Port Configuration**: Host port 8080 conflict
  - Remapped API port from 8080 to 8082
  - Remapped Frontend port from 3000 to 3080
  - Updated all configuration files accordingly
- **Search Service**: Elasticsearch → Meilisearch migration
  - Fixed client initialization to use `Meilisearch\Client` instead of `Elasticsearch\`
  - Updated filter syntax and search parameters
- **JobVision Authentication**: Captcha sign-in permanently disabled
  - Removed `CaptchaToken` from `JobVisionTokenProvider::login()`
  - Updated `.env` configuration to consolidate `talentmatch.jobvision.*` keys

#### Technical Issues Resolved
- **Build Failures**: Vue template syntax errors resolved
- **Container Startup**: Port conflicts resolved, containers now healthy
- **Search Functionality**: All search endpoints operational with Meilisearch
- **Authentication**: Token refresh flow working correctly
- **Error Handling**: User-friendly error modals functional

### Dependencies
- PHP: ^8.4
- Laravel: ^13.0
- Meilisearch PHP Client: ^1.0
- Sanctum: ^4.0
- Node.js: 22.x (for browser agent)

### Infrastructure
- Docker Compose: 5 services (api, frontend, db, redis, meilisearch)
- PostgreSQL 16 with pgvector for vector matching
- Redis for caching
- Meilisearch for search
- Nginx reverse proxy
- E2E testing with Playwright + Cucumber

### Notes
- Production database remains read-only
- New columns/tables require migrations with explicit approval
- All external services use injectable interfaces (mock/external drivers pattern)
- Frontend build requires container restart after changes
- Frontend container mounts host source; production should remove volume mount or rebuild inside container

## Security Considerations
- JobVision credentials stored in database (admin panel)
- Cookie-based auth with expiration checks
- Bearer token authentication for all API endpoints
- Production DB access restricted
- Captcha sign-in permanently disabled for security

## Testing
- Unit tests: All passing
- E2E tests: All 7 scenarios passing
- Integration tests: Search functionality verified
- Manual testing: All endpoints functional

## Migration Notes
- Elasticsearch → Meilisearch replacement
- Port configuration changes (8080→8082, 3000→3080)
- JobVision token refresh implementation
- Frontend build process updates

## Credits
- VNC-based interactive browser: VNC and WebSocket integration
- Browser-assisted login: Playwright automation
- Search service: Meilisearch team
- Error handling: User-friendly modal design
- Testing: E2E test automation
