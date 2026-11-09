# Laravel CRM API

A versioned REST API for a multi-user customer relationship management system, built with **Laravel 13** and **PHP 8.3+**.

> **Project status:** The Laravel application scaffold, versioned API routing, health endpoint, and initial CI workflow are in place. Authentication and CRM workflows are still planned; they are not presented as implemented.

## Current capabilities

- Laravel 13 application bootstrap and environment configuration
- Versioned API routing under `/api/v1`
- JSON health endpoint: `GET /api/v1/health`
- Framework health endpoint: `GET /up`
- MySQL configuration for local development
- SQLite in-memory configuration for automated tests
- PHPUnit feature tests for the API and framework health endpoints
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
| Authentication | Laravel Sanctum (planned) |

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

4. Run migrations and the test suite:

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

## Project documentation

- [API contract and access-control rules](docs/api-contract.md) — design reference for planned endpoints, roles, validation, and response formats.

## Roadmap

- [x] Laravel 13 scaffold, versioned API routing, and health endpoint
- [x] Initial PHPUnit feature tests and GitHub Actions workflow
- [ ] Sanctum authentication, registration, login, logout, and current-user endpoint
- [ ] User roles and authorization policies
- [ ] Customer CRUD, filtering, pagination, and ownership rules
- [ ] Customer notes and follow-up tasks
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
