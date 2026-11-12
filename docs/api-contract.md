# API contract

> Runtime baseline: Laravel 13 and PHP 8.3+. The health endpoint, Sanctum token authentication, customer management, and customer notes are implemented; follow-up tasks remain planned.

> **Status:** Health checks, authentication, customer management, and customer notes are implemented and covered by feature tests. Follow-up task routes below remain design references. Update this document alongside the code when behavior changes.

## Conventions

- Base path: `/api/v1`
- Transport: HTTPS in deployed environments; JSON request and response bodies.
- Authentication: Laravel Sanctum personal access tokens sent as `Authorization: Bearer <token>`.
- Dates: ISO 8601 timestamps in UTC.
- Resource identifiers: numeric IDs initially; do not expose internal database fields that are not part of a resource.
- Collections: paginated with Laravel's length-aware pagination metadata.
- Unknown query parameters should be ignored or rejected consistently; filtering and sorting must use explicit allow-lists.

Authenticated endpoints require a valid token unless marked public. Authentication endpoints should be rate-limited. A user's role must never be accepted from public registration or ordinary profile updates.

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
| Manage notes and tasks | Any customer's records | Own customers' records |
| Manage user roles | Yes | No |

Ownership is enforced on the server through policies, not by trusting an `owner_id` supplied by the client. A member cannot transfer ownership by changing request data. Admin-only operations must be checked independently of UI or route visibility.

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
| DELETE | `/customers/{customer}` | Owner or admin | Delete a customer |

Customer list query parameters:

- `search`: search first name, last name, email, and company.
- `status`: one of the supported status values.
- `per_page`: integer from 1 to 100; default 15.
- `page`: positive page number.
- `sort`: allow-listed values such as `created_at`, `first_name`, and `updated_at`.
- `direction`: `asc` or `desc`.

The API derives the owner from the authenticated user when a member creates a customer. The implemented create/update requests ignore `owner_id`; no owner reassignment endpoint exists. Members see only their own customers, while admins can see all customers. Ownership-protected lookups return 404 for records outside the caller's scope. DELETE uses soft deletion.

## Customer notes (implemented)

Notes are append-oriented records attached to a customer. The API supports creating, listing, and deleting notes; editing note text is intentionally excluded to preserve a simple activity history. Customer owners and admins can list and create notes. A note author, the customer owner, or an admin can delete a note. Nested deletion verifies that the note belongs to the customer in the URL.

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/customers/{customer}/notes` | Customer owner or admin | List notes |
| POST | `/customers/{customer}/notes` | Customer owner or admin | Add a note |
| DELETE | `/customers/{customer}/notes/{note}` | Note author, customer owner, or admin | Delete a note |

A note contains `id`, `customer_id`, `body`, an `author` object with `id` and `name`, `created_at`, and `updated_at`. The author is taken from the authenticated user; client-supplied `user_id` is ignored. The body is required and limited to 5,000 characters. Notes are returned newest first in pages of 15.

## Follow-up tasks

A task belongs to a customer and has a creator. Assignment is optional in v1.

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/tasks` | Authenticated | List tasks visible to the user |
| POST | `/customers/{customer}/tasks` | Customer owner or admin | Create a follow-up |
| GET | `/tasks/{task}` | Assigned user, customer owner, or admin | View a task |
| PATCH | `/tasks/{task}` | Assigned user, customer owner, or admin | Update a task |
| DELETE | `/tasks/{task}` | Creator, customer owner, or admin | Delete a task |

Task fields:

- `title`: required string, 1–160 characters.
- `description`: nullable string, max 2,000 characters.
- `due_at`: nullable ISO 8601 timestamp.
- `assignee_id`: nullable user ID; assignment must be authorized.
- `completed_at`: nullable timestamp; completion is represented by setting or clearing this field.

A member can only create tasks for customers they own. Task list visibility is scoped to tasks the member is assigned to or tasks attached to customers they own. Admins can view all tasks.

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
    "first": "https://example.test/api/v1/customers?page=1",
    "last": "https://example.test/api/v1/customers?page=1",
    "prev": null,
    "next": null
  }
}
```

Validation errors return HTTP 422 with a message and field-keyed errors:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field must be a valid email address."]
  }
}
```

Use standard HTTP status codes consistently:

- `200`: successful read or update.
- `201`: resource created.
- `204`: successful delete or logout with no response body.
- `401`: missing or invalid authentication.
- `403`: authenticated but not allowed.
- `404`: resource does not exist or is outside the caller's visible scope, according to the chosen disclosure policy.
- `422`: validation failure.
- `429`: rate limit exceeded.

Do not leak whether an inaccessible customer's ID exists if the application adopts the 404 disclosure policy for ownership-protected resources.

## Initial relational model

- `users`: identity, password hash, role, timestamps.
- `customers`: owner user, contact fields, status, timestamps, nullable `deleted_at` for soft deletion.
- `customer_notes`: customer, author user, body, timestamps.
- `tasks`: customer, creator, optional assignee, title, description, due/completion timestamps.

Use foreign keys and indexes for common access paths: customer owner and status; notes by customer and creation time; tasks by assignee, customer, and due date. Delete behavior for related records must be explicit in migrations and covered by tests.

## Implementation sequence

1. Scaffold Laravel and configure the test database and CI.
2. Add users, roles, Sanctum authentication, and authentication tests.
3. [x] Add customers, validation, ownership policies, filtering, pagination, soft deletion, and CRUD feature tests.
4. [x] Add customer notes with owner/admin access, author attribution, and nested-route authorization tests.
5. Add follow-up task workflows with assignment and authorization tests.
6. Add queued reminders and notifications after task behavior is stable.
7. Publish request/response examples from verified routes and keep this contract synchronized.

## Open implementation decisions

- Choose whether inaccessible resources return 403 or 404 consistently; this document currently prefers 404 for ownership-scoped lookups.
- The scaffold targets Laravel 13 and PHP 8.3 or newer; keep CI and the local setup guide aligned with the supported runtime.
- Add a controlled admin-provisioning process before deploying the API; public registration intentionally creates members only.
