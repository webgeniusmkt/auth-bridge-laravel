# Product Requirements Document (PRD): Auth Bridge Laravel

## 1. Project Overview

**Project Name:** Auth Bridge Laravel  
**Type:** Laravel Package / Integration Layer  
**Primary Stakeholders:**  
- Platform engineers (maintainers of auth-api and downstream apps)
- App developers (using laravel-app-template)
- AI developer agents (see [AI Collaboration Playbook](../ai/AGENTS.md))

**Summary:**  
Auth Bridge Laravel is a reusable Laravel package that connects any Laravel + Inertia + Svelte app to a central authorization API (**auth-api**). It handles all authentication, registration, 2FA, and user context, so new apps can be launched instantly without custom auth logic. The package syncs a minimal local user record, delegates all token/role/permission logic to the central API, and ships with a ready-made UI (login, registration, etc.) scaffolded into the app.

The laravel-app-template is at ../laravel-app-template/ and the auth-api is at ../auth-api/ so you can see the code there. From the laravel-app-template and it's install.sh script together with this auth-bridge-laravel package I've created the RefinePress app at ../refinepress/.

---

## 2. Goals & Success Metrics

**Business Goals:**
- Enable rapid launch of new Laravel + Inertia + Svelte apps with zero custom authentication code.
- Centralize user management, roles, and permissions for all apps in the ecosystem.
- Reduce onboarding time for new projects to under 10 minutes.

**User Goals:**
- Developers can create a new app from the template and have working auth (login, registration, 2FA, etc.) out of the box.
- End users experience seamless SSO and consistent UX across all apps.

**Success Metrics / KPIs:**
- Time to first authenticated request in a new app < 10 minutes.
- Number of apps using the bridge package.
- Zero duplicated auth logic in downstream apps.

---

## 3. Scope & Non-Scope

**In Scope:**
- Laravel package (`auth-bridge-laravel`) that:
    - Connects to a central auth-api (OAuth2/Passport-based).
    - Publishes migrations to sync local users table.
    - Scaffolds Inertia + Svelte UI for login, registration, 2FA, etc.
    - Handles token validation, user hydration, and role/permission checks via the API.
    - Provides install scripts and onboarding commands.
- Integration with [laravel-app-template](https://github.com/rockstoneaidev/laravel-app-template) for rapid app creation.
- Documentation for AI agents and human developers (see [AI Collaboration Playbook](../ai/AGENTS.md)).

**Out of Scope:**
- The central auth-api itself (see [auth-api repo](https://github.com/rockstoneaidev/auth-api)).
- Custom business logic for downstream apps.
- UI customization beyond the scaffolded defaults (apps can override after install).

---

## 4. Core Architecture Layer

| Framework      | Language | Strategy                    | Database      | Queue / Jobs | Frontend         | Observability      | Deployment           |
|----------------|----------|-----------------------------|---------------|--------------|------------------|--------------------|----------------------|
| Laravel 12.x   | PHP 8.4+ | Auth Bridge + Passport API  | MySQL (local) | Optional     | Inertia + Svelte | Laravel logs, Sentry| Composer/NPM, Docker |

**Key Components:**
- **auth-api**: Central OAuth2/Passport server, user/role management.
- **auth-bridge-laravel**: Bridge package, installed in every app.
- **laravel-app-template**: GitHub template repo, includes install scripts and bridge package.
- **Inertia + Svelte UI**: Provided by the bridge, customizable per app.

---

## 5. Functional Requirements

- **Install/Onboard Flow:**
    - `install.sh` script in template runs:
        - Laravel install
        - Inertia + Svelte install
        - `composer require rockstoneaidev/auth-bridge-laravel`
        - `php artisan auth-bridge:onboard ...` (registers app, sets up .env, publishes UI)
    - See [docs/setup/auth-bridge.md](./setup/auth-bridge.md) for details.

- **User Authentication:**
    - All login, registration, 2FA, and password reset flows handled via bridge package.
    - UI scaffolded into `resources/js/Pages/Auth/` and `resources/js/components/ui/`.
    - Local user table kept in sync for Eloquent relationships.

- **Token & Role Management:**
    - All token validation, refresh, and role/permission checks delegated to central API.
    - Local app never stores or manages passwords/tokens directly.

- **AI Agent Awareness:**
    - All conventions, scripts, and integration points are documented in [ai/AGENTS.md](../ai/AGENTS.md).
    - AI agents should reference this PRD and related docs for context.

---

## 6. Integration & Usage

**For a new app:**
1. Create from [laravel-app-template](https://github.com/rockstoneaidev/laravel-app-template).
2. Run `install.sh` (or manual steps as in [docs/setup/auth-bridge.md](./setup/auth-bridge.md)).
3. App is ready with full auth, user context, and UI.
4. Customize UI as needed after initial scaffold.

**For the central API:**  
See [auth-api/README.md](https://github.com/rockstoneaidev/auth-api/blob/main/README.md) and [docs/setup/testing-apps.md](https://github.com/rockstoneaidev/auth-api/blob/main/docs/setup/testing-apps.md) for details on registering new apps and managing OAuth clients.

---

## 7. References

- [AI Collaboration Playbook](../ai/AGENTS.md)
- [Auth Bridge Integration Guide](./setup/auth-bridge.md)
- [laravel-app-template](https://github.com/rockstoneaidev/laravel-app-template)
- [auth-api](https://github.com/rockstoneaidev/auth-api)
- [Testing Apps Setup](./setup/testing-apps.md)

---

## 8. Change Log

- **2025-11-09:** Major update to reflect real-world architecture and onboarding flow.

---