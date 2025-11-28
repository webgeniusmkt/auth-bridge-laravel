# Auth UI Plan

> **Update (2025-11)**: The Laravel/Inertia auth flows described below now live inside the `rockstoneaidev/auth-bridge-laravel` package and are scaffolded via `php artisan auth-bridge:onboard`. Keep this doc for historical context only.

## Goals
- Provide UX coverage for the Auth API endpoints listed in PRD §3.1 without bloating the shared `rockstoneaidev/auth-bridge-laravel` package.
- Keep all UI, routing, and state management inside `laravel-app-template` (Inertia + Svelte shell) while delegating token verification and `/user` hydration to the Auth Bridge guard.
- Implement iteratively: plan all flows first, then bring `/login` live to unblock local sign-in before expanding to the remaining forms.

## Constraints & Assumptions
- We avoid running `./install.sh` until we are ready to accept the large Laravel skeleton diff. Planning references the generated structure (from `stubs/frontend` and `stubs/app`).
- Frontend stack: Inertia, Svelte, Tailwind, shadcn-svelte components. We'll keep forms under `resources/js/Pages/Auth/` with shared helpers in `resources/js/lib/auth.ts`.
- Backend stack: Laravel controllers under `app/Http/Controllers/Auth/`, request classes in `app/Http/Requests/Auth/`, routes in `routes/web.php` (for SPA) + `routes/api.php` for token helpers if needed.
- Auth API base URL + OAuth client credentials live in `.env` and are consumed through the Auth Bridge HTTP client.
- All API calls must forward `X-Account-ID`/`X-App-Key` headers when context is known. During login/register we only know the app key; account scoping comes after `/user` response.

## Architectural Outline
1. **UI Layer (Inertia Svelte)**
   - Shared `AuthLayout` for hero/branding, reusing components exported from `resources/js/components/ui`.
   - `useForm` from Inertia handles optimistic state, validation errors, and busy states.
   - Toast/Error banner component to unify API failure messaging (hooked into `form.errors` + flash data).

2. **HTTP Client Layer**
   - `resources/js/lib/api.ts` wrapper that posts to Laravel routes (never directly to Auth API from browser). It will include `X-Requested-With` and CSRF token automatically via Inertia.
   - Laravel controllers call the Auth Bridge SDK or raw HTTP client for each remote endpoint.

3. **Server Controllers**
   - `Auth/LoginController`: handles `/login` form submit, calls Auth API `/oauth/token` with password grant, stores tokens (session or cookie), triggers `/user` sync via Auth Bridge guard.
   - Similar controllers for registration, forgot/reset password, profile update, etc., each delegating to small service classes in `app/Services/Auth/`.
   - Responses: redirect back to Inertia route with `form` errors/flash data.

4. **Token Storage Strategy**
   - Store `access_token`, `refresh_token`, `expires_at`, and user payload in encrypted session (`session()->put('auth.token', ...)`).
   - Attach token to subsequent Auth Bridge requests via custom middleware that sets `request()->headers->set('Authorization', 'Bearer ...')` before `auth:api` runs.
   - Refresh flow uses `/oauth/token/refresh` when encountering 401 + expired flag.

## Endpoint-by-Endpoint UI Plan
| Endpoint | UI Entry Point | Data Collected | Server Action | Result/Next Step |
| --- | --- | --- | --- | --- |
| `POST /login` | `/login` page (already stubbed). Add email/password fields, remember-device checkbox, error banner. | email, password, optional `remember`. | `LoginController@store` calls Auth API password grant (`/oauth/token`) using app client id/secret, stores tokens, immediately fetches `/user` to hydrate session, redirects to dashboard. | On success route to `/` (or `intended`). On failure show validation errors, toggle `form.processing=false`. *Implementation target #1.* |
| `POST /register` | `/register` page. Multi-step? Initially single form with name, email, password, optional account/company name. | first_name, last_name, email, password, password_confirmation, `account_name?`. | `RegisterController@store` posts to Auth API `/register`. If API returns immediate token, reuse login path; otherwise direct to `/login` with success flash. | Show confirmation UI; optionally auto-login if tokens issued. |
| `POST /logout` | Header dropdown button. | none; relies on stored refresh token. | `LogoutController@store` calls Auth API `/logout` (token revocation). Clear local session + tokens. | Redirect to `/login` with flash message. |
| `POST /oauth/token` | Hidden behind login; not a UI page. | - | Service method invoked by Login + Refresh flows. Validate `.env` `OAUTH_CLIENT_*`. | Returns token payload to callers. |
| `POST /oauth/token/refresh` | Automatic when SPA detects `401` & refresh token exists. Provide manual "Session expired" toast with retry. | refresh_token | `TokenController@refresh` exchanges refresh token, updates session tokens, returns JSON for SPA to retry. | On failure, force logout. |
| `GET /user` | Loading state on app shell; after login we fetch via Auth Bridge guard to populate `auth` store. Provide `useAuth()` hook in Svelte to expose user/accounts/apps. | - | Middleware ensures request passes stored token to Auth Bridge; guard caches user payload. Provide `/api/v1/user` proxy if SPA needs JSON. | On error redirect to `/login`. |
| `PUT/PATCH /user` | Profile settings page (`/settings/profile`). Fields: avatar upload (optional), name, locale, etc. | first_name, last_name, timezone, locale, avatar. | `ProfileController@update` validates, sends payload to Auth API, updates local cached user. | Show success toast, update Inertia shared props. |
| `POST /forgot-password` | `/forgot-password` page accessible from login. | email | Controller posts to Auth API endpoint, shows success regardless of user existence. | Navigate to "Check your email" screen. |
| `POST /reset-password` | `/reset-password?token=...` page. Inputs: email, password, password_confirmation, token. | same | Controller posts to Auth API. On success, redirect to `/login` with flash. |
| `POST /email/verify` | CTA banner when user not verified. Button sends request to Auth API. | none | Controller posts to `/email/verify`. | Show success message. |
| `POST /email/resend` | Same banner; might reuse verify endpoint. Provide throttle feedback. | none | Controller posts to `/email/resend`. | Flash message. |

## State Management & Shared Utilities
- **Auth Store:** Add `resources/js/lib/authStore.ts` (Svelte store) fed by Inertia shared props; includes `user`, `accounts`, `apps`, `tokenExpiresAt`.
- **Form Error Handling:** Use Inertia form errors + `Alert` component. Provide helper `mapApiErrorsToForm` that normalizes Auth API error structure to Laravel validation errors.
- **Loading/Disable States:** Buttons should show spinner via `Button` component `disabled={form.processing}`.

## Implementation Order
1. Finalize server scaffolding decisions (controllers, services, middleware) without touching files yet.
2. Flesh out `/login` end-to-end (UI form + controller + service + middleware) keeping install footprint minimal.
3. Once login works, replicate service pattern for register, forgot password, etc., one endpoint per PR.

## Next Steps
- Confirm token storage approach with stakeholders (session vs. cookie + SPA store).
- When ready to implement `/login`, run `./install.sh`, commit base skeleton separately, then add our auth files to keep diffs reviewable.
