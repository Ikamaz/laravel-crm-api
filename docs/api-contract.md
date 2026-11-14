# API contract

> Runtime baseline: Laravel 13 and PHP 8.3+. Health checks, Sanctum token authentication, customer management, customer notes, follow-up task workflows, and scheduled queued reminders are implemented and covered by feature tests.

## Conventions

- Base path: `/api/v1`
- Transport: HTTPS in deployed environments; JSON request and response bodies.
- Authentication: Laravel Sanctum personal access tokens sent as `Authorization: Bearer <token>`.
- Dates: ISO 8601 timestamps in UTC.
- Resource identifiers: numeric IDs initially; do not expose internal database fields that are not part of a resource.
- Collections: paginated with Laravel's length-aware pagination metadata.
- Filtering and sorting use explicit allow-lists.

Authenticated endpoints require a valid token unless marked public. Authentication endpoints are rate-limited. A user's role must never be accepted from public registration or ordinary profile updates.

## Health check

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/health` | Public | Confirm that the API application is responding |

With the configured `/api/v1` prefix, the endpoint is available at `GET /api/v1/health`. It returns a `data` object containing `status: ok` and the configured service name. It is an application-level liveness check, not a database or queue readiness check.

## Roles and access rules

The first version has two roles:

| Action | Admin | Member |
| --- | --- | --- |
| List customers | All customers | Own customers |
| View a customer | Any customer | Own customers |
| Create a customer | Yes | Yes; becomes owner |
| Update a customer | Any customer | Own customers |
| Delete a customer | Any customer | Own customers |
| List tasks | All tasks | Tasks for owned customers or assigned to the member |
| Create tasks | Any customer's records | Own customers' records |
| View/update tasks | Any task | Tasks for owned customers or assigned to the member |
| Delete tasks | Any task | Tasks created by the member or attached to their customers |
| Manage notes | Any customer's records | Own customers' records |
| Manage user roles | Yes | No |

Ownership and task access are enforced on the server through policies and query scoping, not by trusting IDs supplied by the client. Ownership-protected resources outside the caller's visible scope return 404.

## Authentication (implemented)

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| POST | `/auth/register` | Public, rate-limited | Create a member account |
| POST | `/auth/login` | Public, rate-limited | Issue an API token |
| POST | `/auth/logout` | Authenticated | Revoke the current token |
| GET | `/auth/me` | Authenticated | Return the current user |

Registration accepts a name, email, password, and password confirmation. Passwords must be 12–72 characters. It always creates a member; clients cannot submit a role. The initial admin account will be provisioned through a controlled seed or administrative process, not public registration.

Login accepts email and password. Successful registration and login return a Sanctum Bearer token and a user data object. Invalid credentials return a validation error. Public registration and login are limited to five requests per minute per IP. Logout revokes only the current token; `GET /auth/me` returns the authenticated user.

## Customers (implemented)

Customer fields:

| Field | Type | Rules |
| --- | --- | --- |
| `first_name` | string | Required, 1–100 characters |
| `last_name` | string | Required, 1–100 characters |
| `email` | string or null | Valid email, max 254 characters |
| `phone` | string or null | Max 30 characters |
| `company` | string or null | Max 150 characters |
| `position` | string or null | Max 120 characters |
| `source` | string or null | Max 80 characters |
| `status` | enum | `lead`, `contact`, `customer`, or `inactive` |

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/customers` | Authenticated | List accessible customers |
| POST | `/customers` | Authenticated | Create a customer |
| GET | `/customers/{customer}` | Owner or admin | View a customer |
| PATCH | `/customers/{customer}` | Owner or admin | Update editable fields |
| DELETE | `/customers/{customer}` | Owner or admin | Soft-delete a customer |

Customer list query parameters:

- `search`: search first name, last name, email, and company.
- `status`: one of the supported status values.
- `per_page`: integer from 1 to 100; default 15.
- `page`: positive page number.
- `sort`: allow-listed values such as `created_at`, `first_name`, and `updated_at`.
- `direction`: `asc` or `desc`.

The API derives the owner from the authenticated user when a member creates a customer. The implemented create/update requests ignore `owner_id`; no owner reassignment endpoint exists. Members see only their own customers, while admins can see all customers. DELETE uses soft deletion.

## Customer notes (implemented)

Notes are append-oriented records attached to a customer. The API supports creating, listing, and deleting notes; editing note text is intentionally excluded to preserve a simple activity history. Customer owners and admins can list and create notes. A note author, the customer owner, or an admin can delete a note. Nested deletion verifies that the note belongs to the customer in the URL.

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/customers/{customer}/notes` | Customer owner or admin | List notes |
| POST | `/customers/{customer}/notes` | Customer owner or admin | Add a note |
| DELETE | `/customers/{customer}/notes/{note}` | Note author, customer owner, or admin | Delete a note |

A note contains `id`, `customer_id`, `body`, an `author` object with `id` and `name`, `created_at`, and `updated_at`. The author is taken from the authenticated user; client-supplied `user_id` is ignored. The body is required and limited to 5,000 characters. Notes are returned newest first in pages of 15.

## Follow-up tasks (implemented)

A task belongs to a customer and has a creator. Assignment is optional.

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/tasks` | Authenticated | List tasks visible to the user |
| POST | `/customers/{customer}/tasks` | Customer owner or admin | Create a follow-up |
| GET | `/tasks/{task}` | Assigned user, customer owner, or admin | View a task |
| PATCH | `/tasks/{task}` | Assigned user, customer owner, or admin | Update task details or completion |
| DELETE | `/tasks/{task}` | Creator, customer owner, or admin | Delete a task |

Task fields:

| Field | Type | Rules |
| --- | --- | --- |
| `title` | string | Required on create, 1–160 characters |
| `description` | string or null | Optional, max 2,000 characters |
| `due_at` | timestamp or null | Optional date/time |
| `assignee_id` | integer or null | Optional existing user; only owner/admin may change assignment |
| `completed_at` | timestamp or null | Set to complete or clear to reopen; not accepted on create |

The server assigns `created_by`; clients cannot choose the task creator. A member can only create tasks for customers they own. The customer owner or an admin may assign a task to an existing user. An assigned user may view and update task details, including completion, but cannot change the assignee or delete the task solely because they are assigned.

Task list visibility is scoped to tasks attached to customers owned by the member or tasks assigned to them. Admins can view all tasks. Supported filters are:

- `status=open`: tasks with no completion timestamp.
- `status=completed`: tasks with a completion timestamp.
- `status=overdue`: incomplete tasks with a due date earlier than the current time.
- `per_page`: integer from 1 to 100; default 15.
- `page`: positive page number.
- `sort`: `created_at`, `updated_at`, or `due_at`.
- `direction`: `asc` or `desc`.

Task resources include task fields, a customer summary, creator and assignee summaries, and ISO 8601 timestamps. The list is paginated.

### Task reminders (implemented)

The scheduler runs `tasks:send-due-reminders` every minute. The command selects incomplete tasks with a due date from now through the next 24 hours and queues a `TaskDueReminder` notification for the assignee. If the task has no assignee, the creator is notified. Completed, overdue, and undated tasks are skipped. The internal `reminder_dispatched_at` timestamp prevents repeat dispatch for the same task.

Reminders use Laravel's database notification channel and the database queue. The notification payload contains `type`, `task_id`, `customer_id`, `title`, and `due_at`. A queue worker must be running for queued notifications to be processed. Failed jobs are stored in `failed_jobs`. Reading notifications through the API and marking them as read are not implemented in this version.

## Response format

A successful single-resource response uses a `data` object:

```json
{
  "data": {
    "id": 42,
    "first_name": "Ada",
    "last_name": "Lovelace",
    "status": "lead"
  }
}
```

A collection response uses a `data` array and pagination metadata:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 0
  },
  "links": {
    "first": "https://example.test/api/v1/tasks?page=1",
    "last": "https://example.test/api/v1/tasks?page=1",
    "prev": null,
    "next": null
  }
}
```

Validation errors return HTTP 422 with a message and field-keyed errors. Standard status codes are used consistently: 200 for successful reads/updates, 201 for creation, 204 for deletion, 401 for missing/invalid authentication, 403 for authenticated but disallowed operations, 404 for missing or inaccessible resources, 422 for validation failures, and 429 for rate limits.

## Initial relational model

- `users`: identity, password hash, role, timestamps.
- `customers`: owner user, contact fields, status, timestamps, nullable `deleted_at` for soft deletion.
- `customer_notes`: customer, author user, body, timestamps.
- `tasks`: customer, creator, optional assignee, title, description, due/completion timestamps, and internal reminder dispatch timestamp.
- `notifications`: Laravel database notifications addressed to users.
- `jobs` and `failed_jobs`: pending and failed database-queue jobs.

Foreign keys define deletion behavior: deleting a customer removes its notes and tasks; deleting an assignee clears the assignment; deleting a task creator is restricted while their tasks remain. Indexes support customer ownership/status, notes by customer and creation time, and task lookup by customer, assignee, completion, and due date.

## Implementation sequence

1. [x] Scaffold Laravel and configure the test database and CI.
2. [x] Add users, roles, Sanctum authentication, and authentication tests.
3. [x] Add customers, validation, ownership policies, filtering, pagination, soft deletion, and CRUD feature tests.
4. [x] Add customer notes with owner/admin access, author attribution, and nested-route authorization tests.
5. [x] Add follow-up task workflows with assignment and authorization tests.
6. [x] Add scheduled task reminders, queued database notifications, and database queue migrations.
7. Add authenticated notification listing and read-state endpoints with authorization and tests.
8. Publish additional request/response examples from verified routes and keep this contract synchronized.

## Remaining implementation decisions

- Add a controlled admin-provisioning process before deploying the API; public registration intentionally creates members only.
- Add a reproducible dependency lockfile and Docker development environment.
- Add authenticated endpoints for listing notifications and marking them as read.
- Add operational queue monitoring and a production deployment guide.
