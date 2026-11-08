# Laravel CRM API

A portfolio project for a multi-user customer relationship management API built with Laravel 12 and PHP 8.2+.

> **Status:** Design and initial setup in progress. Features will be marked complete only after implementation and tests.

## Planned capabilities

- Token-based authentication and account management
- Role-aware access control and customer ownership
- Customer records, contact details, notes, and follow-up tasks
- Request validation and consistent JSON error responses
- Background jobs and notifications for follow-ups
- Feature tests for authentication, authorization, and CRUD workflows
- Docker-friendly local development and CI checks

## Tech stack

- PHP 8.2+
- Laravel 12
- MySQL 8+
- Pest for automated tests
- Laravel Sanctum for API tokens (planned)

## Project documentation

- [API contract and access-control rules](docs/api-contract.md) — planned endpoints, roles, validation rules, response shapes, and implementation sequence.

The API contract is a design reference at this stage. The application scaffold and endpoints have not been implemented yet.

## Local setup

The application scaffold and exact setup instructions will be added in the first implementation milestone.

## API

Versioned endpoints will use the `/api/v1` prefix. Request examples in the [API contract](docs/api-contract.md) are illustrative until the corresponding endpoints are implemented and tested.

## Project principles

- Keep authorization checks on the server.
- Validate all incoming data.
- Prefer small, focused controllers and reusable domain logic.
- Cover important behavior with automated tests.
- Do not claim functionality is complete until it has been verified.

## Roadmap

1. [ ] Laravel application scaffold and CI
2. [ ] Authentication and user roles
3. [ ] Customer CRUD with ownership policies
4. [ ] Notes and follow-up tasks
5. [ ] Queued reminders and notifications
6. [ ] Verified API examples and expanded test coverage

## License

MIT
