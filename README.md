# Laravel CRM API

A versioned REST API for a multi-user customer relationship management system, built with **Laravel 13** and **PHP 8.3+**.

> **Project status:** Authentication, customer CRUD, customer notes, follow-up task workflows, authorization policies, queued task reminders, an authenticated notification inbox, feature tests, and GitHub Actions CI are implemented. Docker and a committed dependency lockfile remain planned.

## Current capabilities

- Laravel 13 application bootstrap and environment configuration
- Versioned API routing under `/api/v1`
- JSON health endpoint: `GET /api/v1/health`
- MySQL configuration for local development
- SQLite in-memory configuration for automated tests
- Sanctum personal access tokens for registration, login, logout, and the current-user endpoint
- Rate-limited public authentication routes and protected token-authenticated routes
- Server-controlled `member` role assigned during registration
- Customer CRUD with owner-scoped member access and admin-wide access
- Customer notes with author attribution, owner/admin access rules, and append-only text
- Follow-up tasks with optional assignees, due dates, completion tracking, and access policies
- Scheduled reminders for incomplete tasks due within 24 hours, queued to the assignee or task creator through Laravel database notifications
- Authenticated notification inbox with per-user pagination, individual read state, and mark-all-as-read support
- Database-backed queue tables for pending and failed jobs
- Task list visibility scoped to owned customers and tasks assigned to the authenticated member
- Task filtering by open, completed, or overdue status
- Customer search, status filtering, allow-listed sorting, and pagination
- Validated request payloads and consistent JSON API resources
- PHPUnit feature tests for authentication, customer, note, and task workflows
- GitHub Actions workflow for PHP 8.3, 8.4, and 8.5
- API contract documenting implemented endpoints, access rules, and planned background workflows

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
| Queue | Laravel database queue with a worker and scheduled reminder command |
| Notifications | Queued Laravel database notifications |

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

6. In separate terminals, run the queue worker and local scheduler:

   ```bash
   php artisan queue:work
   php artisan schedule:work
   ```

The API will be available at `http://127.0.0.1:8000/api/v1`. In production, run Laravel's scheduler every minute with `php artisan schedule:run` from the system cron service.

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

## Follow-up tasks

All task endpoints require a Sanctum Bearer token.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/v1/tasks` | List tasks visible to the authenticated user |
| POST | `/api/v1/customers/{customer}/tasks` | Create a follow-up task |
| GET | `/api/v1/tasks/{task}` | Retrieve a task |
| PATCH | `/api/v1/tasks/{task}` | Update task details or completion |
| DELETE | `/api/v1/tasks/{task}` | Delete a task when authorized |

Task creation requires the customer owner or an admin. The server assigns the creator; clients cannot set `created_by` or `completed_at` during creation. Assignment is optional and uses `assignee_id`. Only the customer owner or an admin can change the assignee. The assignee can view and update a task, including marking it complete, but cannot reassign or delete it solely because they are assigned.

Members see tasks attached to customers they own or tasks assigned to them. Admins can see all tasks. The list supports `status=open`, `status=completed`, and `status=overdue`, plus `per_page`, `page`, `sort`, and `direction`. Page size is limited to 100.

Example:

```http
POST /api/v1/customers/42/tasks
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json

{
  "title": "Call about the proposal",
  "description": "Confirm the revised delivery date.",
  "due_at": "2026-11-20T10:00:00Z",
  "assignee_id": 7
}
```

Set `completed_at` to an ISO 8601 timestamp to complete a task, or set it to `null` to reopen it. Due dates and completion timestamps are returned in ISO 8601 format.

## Task reminders and background processing

Incomplete tasks with a due date between the current time and the next 24 hours are considered for a reminder. The scheduled command runs every minute and queues one database notification for the assignee; if the task is unassigned, the creator receives it instead. A `reminder_dispatched_at` timestamp prevents the same task from being queued repeatedly. Completed, overdue, and undated tasks are skipped.

Run a queue worker to process notifications. The notification is stored in Laravel's `notifications` table and can be retrieved through the authenticated notification API. Failed queue jobs are recorded in `failed_jobs` for inspection and retry.

You can run the command manually with:

```bash
php artisan tasks:send-due-reminders
```

## Notifications

All notification endpoints require a Sanctum Bearer token and are scoped to the authenticated user.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/v1/notifications` | List the current user's notifications, newest first |
| PATCH | `/api/v1/notifications/{notification}/read` | Mark one of the current user's notifications as read |
| PATCH | `/api/v1/notifications/read-all` | Mark all of the current user's unread notifications as read |

The list endpoint accepts `per_page` from 1 to 100 (default 15) and returns standard Laravel pagination metadata. Attempts to mark another user's notification as read return 404. Marking an already-read notification is safe to repeat. The mark-all endpoint returns the number of unread notifications updated.

## Project documentation

- [API contract and access-control rules](docs/api-contract.md) — endpoint behavior, authorization rules, validation, and remaining planned workflows.

## Roadmap

- [x] Laravel 13 scaffold, versioned API routing, and health endpoint
- [x] Initial PHPUnit feature tests and GitHub Actions workflow
- [x] Sanctum authentication, registration, login, logout, and current-user endpoint
- [x] User roles and authorization policies
- [x] Customer CRUD, filtering, pagination, and ownership rules
- [x] Customer notes
- [x] Follow-up tasks, assignment, completion, filtering, and access policies
- [x] Queued task reminders, database notifications, and scheduled dispatch
- [x] Authenticated notification inbox, per-user read state, and pagination
- [ ] Docker development environment
- [ ] Commit Composer lockfile and expand API documentation with verified response examples

## Engineering principles

- Enforce authorization on the server; never trust client-supplied ownership, creator, or role fields.
- Validate all incoming data and return predictable JSON errors.
- Keep controllers focused and move reusable business rules into appropriate classes.
- Cover important behavior with feature tests.
- Mark functionality complete only after implementation and verification.

## License

MIT. See [LICENSE](LICENSE).
