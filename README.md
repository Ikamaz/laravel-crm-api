# Laravel CRM API

A versioned REST API for a multi-user customer relationship management system, built with **Laravel 13** and **PHP 8.3+**.

> **Project status:** The Laravel 13 scaffold, versioned API, health check, Sanctum token authentication, customer CRUD, ownership policies, customer notes, and CI workflow are implemented. Follow-up task workflows remain planned.

## Current capabilities

- Laravel 13 application bootstrap and environment configuration
- Versioned API routing under `/api/v1`
- JSON health endpoint: `GET /api/v1/health`
- Framework health endpoint: `GET /up`
- MySQL configuration for local development
- SQLite in-memory configuration for automated tests
- Sanctum personal access tokens for registration, login, logout, and the current-user endpoint
- Rate-limited public authentication routes and protected token-authenticated routes
- Server-controlled `member` role assigned during registration
- Customer CRUD with owner-scoped member access and admin-wide access
- Customer notes with author attribution, owner/admin access rules, and append-only text
- Customer search, status filtering, allow-listed sorting, and pagination
- Soft deletion, validated request payloads, and JSON API resources
- PHPUnit feature tests for health checks and authentication flows
- GitHub Actions workflow for PHP 8.3, 8.4, and 8.5
- API contract documenting planned authentication, roles, customers, notes, and tasks

## Tech stack

| Area | Technology |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| Database | MySQL 8+ (development), SQLite (tests) |
| API | REST, JSON, versioned routes |
| Testing | PHPUnit 12 |
| Code style | Laravel Pint |
| CI | GitHub Actions |
| Authentication | Laravel Sanctum personal access tokens |

## Requirements

- PHP 8.3 or newer with the extensions required by Laravel
- Composer 2
- MySQL 8+ for local development
- SQLite PDO extension for the automated test suite

## Local setup

1. Clone the repository and install dependencies:

   ```bash
   git clone https://github.com/Ikamaz/laravel-crm-api.git
   cd laravel-crm-api
   composer install
   ```

2. Create the local environment file and application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Create a MySQL database named `laravel_crm_api`, then set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

4. Run migrations (including the Sanctum personal access token table) and the test suite:

   ```bash
   php artisan migrate
   php artisan test
   vendor/bin/pint --test
   ```

5. Start the local server:

   ```bash
   php artisan serve
   ```

The API will be available at `http://127.0.0.1:8000/api/v1`.

## API

### Health check

```http
GET /api/v1/health
Accept: application/json
```

Example response:

```json
{
  "data": {
    "status": "ok",
    "service": "Laravel CRM API"
  }
}
```

Try it locally:

```bash
curl -H "Accept: application/json" http://127.0.0.1:8000/api/v1/health
```

The endpoint confirms that the Laravel application and API routing are responding. It does not yet perform database or queue dependency checks.

### Authentication

Public registration and login are limited to 5 requests per minute per IP address.

Register:

```http
POST /api/v1/auth/register
Content-Type: application/json
Accept: application/json

{
  "name": "Example User",
  "email": "user@example.com",
  "password": "a-strong-password-123",
  "password_confirmation": "a-strong-password-123"
}
```

Login uses `POST /api/v1/auth/login` with `email` and `password`. Both endpoints return a Bearer token. Registration always assigns the `member` role; a client-supplied role is ignored.

Send the returned token on protected requests:

```http
Authorization: Bearer <token>
Accept: application/json
```

- `GET /api/v1/auth/me` returns the authenticated user.
- `POST /api/v1/auth/logout` revokes only the token used for that request.

Passwords must be at least 12 characters and include a matching confirmation during registration. Store issued tokens securely; the plain-text token is returned only when it is created.

## Customers

All customer endpoints require a Sanctum Bearer token.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/v1/customers` | List customers visible to the authenticated user |
| POST | `/api/v1/customers` | Create a customer owned by the authenticated user |
| GET | `/api/v1/customers/{id}` | Retrieve a customer |
| PATCH | `/api/v1/customers/{id}` | Update customer fields |
| DELETE | `/api/v1/customers/{id}` | Soft-delete a customer |

Members can access only their own customers. Admins can access all customers. Attempts to access another member's customer return 404. The server assigns ownership during creation; `owner_id` in a request is ignored and is never accepted as an editable field.

The list endpoint supports `search`, `status`, `per_page`, `page`, `sort`, and `direction`. Page size is limited to 100, and sorting is restricted to an explicit allow-list.

Example:

```http
GET /api/v1/customers?search=ada&status=lead&per_page=15
Authorization: Bearer <token>
Accept: application/json
```

Customer records are soft-deleted so a normal DELETE request does not immediately remove their database row. The resource response omits the internal owner foreign key.

## Customer notes

All note endpoints require a Sanctum Bearer token.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/v1/customers/{customer}/notes` | List a customer's notes (paginated, newest first) |
| POST | `/api/v1/customers/{customer}/notes` | Add a note to a customer |
| DELETE | `/api/v1/customers/{customer}/notes/{note}` | Delete a note when authorized |

A note body is required and limited to 5,000 characters. The API assigns the author from the authenticated user; client-supplied `user_id` values are ignored. Customer owners and admins can list and add notes. A note author, the customer owner, or an admin can delete a note. A note cannot be deleted through a different customer's nested route.

## Project documentation

- [API contract and access-control rules](docs/api-contract.md) — design reference for planned endpoints, roles, validation, and response formats.

## Roadmap

- [x] Laravel 13 scaffold, versioned API routing, and health endpoint
- [x] Initial PHPUnit feature tests and GitHub Actions workflow
- [x] Sanctum authentication, registration, login, logout, and current-user endpoint
- [x] User roles and authorization policies
- [x] Customer CRUD, filtering, pagination, and ownership rules
- [x] Customer notes
- [ ] Follow-up tasks
- [ ] Queued reminders and notifications
- [ ] Docker development environment and expanded API documentation

## Engineering principles

- Enforce authorization on the server; never trust client-supplied ownership or role fields.
- Validate all incoming data and return predictable JSON errors.
- Keep controllers focused and move reusable business rules into appropriate classes.
- Cover important behavior with feature tests.
- Mark functionality complete only after implementation and verification.

## License

MIT. See [LICENSE](LICENSE).
