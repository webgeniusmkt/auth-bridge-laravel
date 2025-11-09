# 🧾 Product Requirements Document (PRD) Template

> Replace the placeholders (`{{LIKE_THIS}}`) with the details for your application. Example text shown in *italics* is optional guidance—remove it once the real information is known. Check https://github.com/webgeniusmkt/auth-api/blob/main/docs/app-prd.md out to get some inspiration.

---

## 1. Product Requirements Document (PRD)
- **Project Name:** `{{PROJECT_NAME}}` *Example: Atlas Dashboard*
- **Type:** `{{PROJECT_TYPE}}` *Example: B2B SaaS analytics portal*
- **Primary Stakeholders:** `{{STAKEHOLDERS}}`
- **Summary:** `{{ONE_PARAGRAPH_SUMMARY}}`

---

## 2. Goals & Success Metrics
- **Business Goals:** `{{BUSINESS_GOALS}}` *Example: Reduce onboarding time by 30%*
- **User Goals:** `{{USER_GOALS}}`
- **Success Metrics / KPIs:** `{{KPIS}}`

---

## 3. Scope & Non-Scope
- **In Scope:** `{{WHAT_IS_INCLUDED}}`
- **Out of Scope:** `{{WHAT_IS_EXCLUDED}}`

---

## 4. Core Architecture

| Layer | Choice / Notes |
|-------|----------------|
| Framework | `{{FRAMEWORK}}` *Default: Laravel 12* |
| Language | `{{LANGUAGE}}` *Default: PHP 8.4* |
| Auth Strategy | `{{AUTH_STRATEGY}}` *Default: Auth Bridge + Passport* |
| Database | `{{DATABASE}}` *Example: MySQL 8 + Redis cache* |
| Queue / Jobs | `{{QUEUE}}` |
| Frontend | `{{FRONTEND}}` *Example: Vite + Vue* |
| Observability | `{{LOGGING_METRICS}}` |
| Deployment | `{{DEPLOYMENT_PIPELINE}}` |

Add, remove, or expand rows as needed for the project.

---

## 5. Functional Requirements
Break down the feature areas relevant to this app. Use the tables below as a starting point.

### 5.1 `{{FEATURE_AREA_NAME}}`
| ID | User Story | Acceptance Criteria |
|----|------------|--------------------|
| `FR-{{001}}` | `{{As a <role>, I want ...}}` | `{{Given/When/Then ...}}` |
| | | |

### 5.2 `{{ADDITIONAL_FEATURE_AREA}}`
| ID | User Story | Acceptance Criteria |
|----|------------|--------------------|
| | | |

Repeat as many sections as necessary. Consider referencing shared requirements such as [Pagination, Sorting & Filtering](requirements/pagination-in-controllers.md) when applicable.

---

## 6. API & Integration Notes
- **Authentication Guard:** `{{AUTH_GUARD}}` *Example: auth-bridge guard (see docs/setup/auth-bridge.md)*
- **External Services:** `{{SERVICE_DEPENDENCIES}}` *Example: Billing API, Notifications service*
- **Key Endpoints:** `{{ENDPOINT_LIST}}` *Provide route, method, purpose*
- **Headers / Context Requirements:** `{{HEADER_NOTES}}` *Example: X-Account-ID, X-App-Key*

If the app introduces new APIs, document example requests/responses here.

---

## 7. Data Model Overview

| Entity | Key Fields | Notes |
|--------|------------|-------|
| `{{Entity}}` | `{{fields}}` | `{{relationships / statuses}}` |
| | | |

Include ERD links or diagrams if available.

---

## 8. Non-Functional Requirements
- **Performance / SLAs:** `{{PERFORMANCE}}`
- **Security / Compliance:** `{{SECURITY}}`
- **Reliability / Uptime:** `{{RELIABILITY}}`
- **Scalability Considerations:** `{{SCALABILITY}}`
- **Accessibility:** `{{ACCESSIBILITY}}`
- **Localization:** `{{LOCALE_REQUIREMENTS}}`

---

## 9. Testing & Quality Strategy
- **Test Types:** `{{TEST_TYPES}}` *Example: Pest feature tests, contract tests*
- **Coverage Targets:** `{{COVERAGE_TARGETS}}`
- **Tooling:** `{{TEST_TOOLING}}`
- **Manual QA Checklist:** `{{MANUAL_QA}}`

---

## 10. Milestones & Timeline
| Milestone | Description | Owner | Target Date |
|-----------|-------------|-------|-------------|
| `{{M1}}` | `{{Description}}` | `{{Owner}}` | `{{Date}}` |
| | | | |

---

## 11. Open Questions & Risks
- **Open Questions:** `{{OPEN_QUESTIONS}}`
- **Risks / Mitigations:** `{{RISKS_AND_MITIGATIONS}}`

---

## 12. Appendices
- Link supporting documents, diagrams, or decision records: `{{LINKS}}`
- Reference any workflow notes stored in `/ai/workflows/`

---

> ✅ Once the project details are finalized, remove unused sections/placeholders so the PRD reflects the actual application scope.
