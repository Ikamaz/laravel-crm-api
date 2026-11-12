# Agency platform foundation

This repository is being extended into a bilingual digital agency website and internal lead-management API.

## Public website

- Georgian landing page: `/`
- English landing page: `/en`
- Contact form: `POST /leads`
- Form submissions are rate-limited, validated, and stored in the `inbound_leads` table.
- The form requires an email address or phone number and explicit enquiry-response consent.
- A honeypot field provides basic bot filtering. It is not a substitute for a production anti-abuse service.

## CRM API

All endpoints use the existing versioned API prefix `/api/v1`.

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/api/v1/inbound-leads` | Sanctum + admin | List leads, optionally filter by status |
| GET | `/api/v1/inbound-leads/{id}` | Sanctum + admin | View a lead |
| PATCH | `/api/v1/inbound-leads/{id}` | Sanctum + admin | Update lead status |

Supported statuses: `new`, `contacted`, `qualified`, `converted`, `closed`.

Member accounts cannot read or update inbound leads. The existing customer endpoints and their ownership rules remain unchanged.

## Local setup

1. Install the existing Laravel dependencies with Composer.
2. Copy `.env.example` to `.env` and configure a local database.
3. Run `php artisan key:generate`.
4. Run `php artisan migrate`.
5. Run `php artisan test`.
6. Start the local server with `php artisan serve`.

Open `http://127.0.0.1:8000` for Georgian or `http://127.0.0.1:8000/en` for English.

## Next implementation steps

- Add feature tests for public lead submission and admin-only API access.
- Add a secure internal CRM dashboard for lead triage.
- Add conversion of a qualified inbound lead into a CRM customer without duplicating contact records.
- Add notification delivery after choosing a client-funded email provider.
- Add privacy notice, retention rules, and deployment hardening before collecting real customer data.

## Zero-budget deployment principle

Develop and demonstrate locally first. Production hosting, domain registration, outbound email, and any paid AI API usage should be purchased by the agency owner or paid directly by the client. Do not expose the n8n editor or CRM administration to the public internet without authentication and deployment hardening.
