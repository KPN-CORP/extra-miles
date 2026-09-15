# Extra Miles - Handover Guide for the New PIC

This document summarizes the repository structure, technical stack, coding conventions, and branching/deployment approach for the Extra Miles project.

## Project Summary

- **Project name:** Extra Miles
- **Primary stack:** Laravel 11, PHP 8.2, Livewire, Blade, React 19, Vite, Tailwind CSS, Bootstrap 5
- **Repository:** GitHub repository owned by KPN-CORP
- **Current branch:** `gazteruz`
- **Default branch:** `main`

## 1. Architecture Overview

### Backend

The backend is a Laravel application with a standard MVC structure:

- Controllers live in `app/Http/Controllers`
- Livewire components live in `app/Livewire`
- Eloquent models are stored in `app/Models`
- Database schema changes are handled through migrations in `database/migrations`

Most request handling is still done in controllers and Livewire components rather than a separate service/repository layer.

### Frontend

The project has two frontend patterns:

- **Admin/back-office UI:** Blade templates with Livewire and traditional Laravel views in `resources/views`
- **Mobile-facing UI:** React-based app under `resources/js/users`, with pages in `resources/js/users/pages` and shared logic/components in `resources/js/users/components`

The frontend is built and bundled with Vite via `vite.config.js`.

## 2. Coding Conventions

### Backend Conventions

- **Class and controller naming:** PascalCase
  - Example: `EventController`
- **Variables and methods:** camelCase
  - Example: `$eventsToUpdate`, `showQRPNG()`
- **Database tables:** snake_case, plural names
  - Example: `events`, `event_participants`
- **Formatting:** Laravel Pint is used for PHP formatting.
  - Run this before committing:

```bash
./vendor/bin/pint
```

### Frontend Conventions

- **React component files:** PascalCase
  - Example: `EventDetails.jsx`
- **Functions and state variables:** camelCase
  - Example: `useState`, `useEffect`, `AnimatedRoutes`
- **Styling:** Tailwind and Bootstrap are both used, depending on the UI area.

### API Response Style

The project uses simple JSON responses rather than a strict standardized wrapper format.

#### Success example

```json
{
  "message": "Login success",
  "token": "<jwt-token>",
  "token_type": "Bearer"
}
```

#### Error example

```json
{
  "error": "Validation failed",
  "messages": {
    "field_name": ["This field is required."]
  }
}
```

## 3. Repository and Git Workflow

### Git Remote

The repository is configured with GitHub remote:

- `https://github.com/KPN-CORP/extra-miles.git`

### Branching Strategy

The project appears to use a branch-per-feature or branch-per-developer approach.

Recommended workflow:

1. Create a dedicated feature branch from the active development branch.
2. Keep commits focused and descriptive.
3. Run formatting and relevant validation before pushing.
4. Open a pull request for review before merging into the main development branch.

### Current Git Setup

The local repository is currently tracking:

- Branch: `gazteruz`
- Remote tracking branch: `origin/gazteruz`

## 4. Deployment and CI/CD

### Deployment

The deployment process is configured through `.cpanel.yml`.

It currently:

- Copies the Laravel application directories to the deployment path
- Copies built public assets to the live web root
- Clears Laravel caches after deployment

### CI/CD

No GitHub Actions or GitLab CI pipeline files were found in the repository. Deployment is currently managed through the cPanel deployment configuration rather than an automated pipeline.

## 5. Practical Development Notes

- Use Laravel conventions for routes, controllers, models, and migrations.
- Keep business logic near the relevant controller or Livewire component unless the code clearly belongs in a shared service.
- For UI work, prefer the existing Laravel + Blade/Livewire structure for admin pages and the React/Vite structure for the mobile app.
- Before committing PHP changes, run Pint.
- Before deploying frontend changes, ensure the Vite build succeeds.

### Local Sign-In Without SSO

Both frontends normally authenticate through Darwinbox, which a developer machine
cannot reach. Setting `DEVELOPMENT_MODE=keydevelopment` in `.env` exposes two local
stand-ins; without it they 404 and both entry points behave exactly as in production.

- `GET /admin/login` renders a local form instead of bouncing to Darwinbox
  (`Auth\DevLoginController`). Sign in as any `kpncorp` user by Employee ID or email.
  The password field is optional, because most SSO accounts have no usable hash.
- `GET /dev/mobile-login` mints a JWT for an employee and redirects to
  `/login-success?token=...`, the same landing route the real auth-service uses
  (`Auth\DevMobileLoginController`). Tick "Show the token instead of opening the app"
  to get the raw JWT for curl. The token carries `sub`, `employee_id` and `fullname`,
  the only claims the API reads — `employee_id` especially, since every mobile
  endpoint takes it from the payload rather than from the authenticated user.

Two things to get right when testing the employee app:

- The SPA only renders on a touch device narrower than 768px. Turn on the browser's
  device emulation *before* signing in, or you land on the "Mobile Only" screen and
  the token is never stored.
- `VITE_API_URL` is read when Vite starts or builds, not per request. If `APP_URL`
  does not match the host and port you are actually serving on, every API call from
  the SPA goes to the wrong origin and fails.

## 6. Suggested First Steps for the New PIC

- Review the main Laravel routes in `routes/web.php` and `routes/api.php`
- Inspect the event-related flow in `app/Http/Controllers/EventController.php`
- Review the mobile app entrypoint in `resources/js/users/app.jsx`
- Check the deployment target in `.cpanel.yml`
- Confirm environment variables and storage requirements before making changes
