# CoreOps WP Plugin — Effort Breakdown

**Date:** 2026-05-30
**Project:** CoreOps-WP-Plugin
**Integration target:** CoreOps-Base REST API

---

## Overview

A WordPress plugin that displays a calendar populated from the CoreOps-Base API, allows visitors to search availability, and submit bookings — all authenticated server-side via a Personal Access Token (PAT).

---

## Open Questions (resolve before starting)

| # | Question | Why it matters |
|---|---|---|
| 1 | What is a "booking"? Appointment slot, event registration, resource reservation? | Determines the CoreOps-Base data model and API shape |
| 2 | Who can book? Anonymous WP visitors or logged-in WP users tied to CoreOps accounts? | Determines auth model and whether user mapping is needed |
| 3 | Calendar display style — month grid, week view, agenda list, or date-picker only? | Determines frontend library choice and complexity |
| 4 | Gutenberg block, classic shortcode, or both? | Affects build tooling and PHP structure |
| 5 | Should the plugin expose admin controls to view/manage bookings from WP, or read-only? | Determines scope of the admin UI |

---

## Architecture

```
WordPress Site                          CoreOps-Base
─────────────────────────────           ─────────────────────────
Plugin Settings (WP Admin)              REST API (/api/*)
  └─ API Base URL                            GET /api/calendar-events  [NEW]
  └─ PAT Token (stored encrypted)            GET /api/availability
  └─ Calendar config                         POST /api/bookings         [NEW]

WP REST Proxy (PHP, server-side)
  └─ GET /wp-json/coreops/v1/events     →   CoreOps API (with PAT)
  └─ GET /wp-json/coreops/v1/availability → CoreOps API (with PAT)
  └─ POST /wp-json/coreops/v1/book      →   CoreOps API (with PAT)

Frontend (JS + FullCalendar or custom)
  └─ Fetches from WP REST Proxy (no PAT exposed to browser)
  └─ Availability search form
  └─ Booking form
```

**Key principle:** The PAT never leaves the server. WordPress acts as a proxy — the browser calls WordPress REST endpoints, WordPress calls CoreOps with the PAT attached.

---

## CoreOps-Base Changes Required

### WP.0 — New API endpoints in CoreOps-Base

| Endpoint | Method | Description |
|---|---|---|
| `/api/calendar-events` | GET | List events for a date range — for populating the calendar feed |
| `/api/bookings` | POST | Create a new booking/calendar event from an external caller |

**Effort:** 4h
**Notes:** `GET /api/availability` already exists. These two additions are self-contained Hono routes wired to existing `lib/db.ts` query functions. Authentication and RBAC already handled by existing middleware.

---

## Plugin Phases

### WP.1 — Plugin scaffold & settings

Create the plugin foundation:
- `coreops-wp-plugin.php` — main plugin file with header, activation/deactivation hooks
- Settings page under **Settings → CoreOps** with fields for API base URL and PAT (stored via `update_option`, PAT encrypted at rest)
- Connection test button (calls `/api/health`, shows status)
- `uninstall.php` — cleans up options on deletion

**Effort:** 4h

---

### WP.2 — WordPress REST proxy

Server-side PHP endpoints that forward requests to CoreOps-Base with the stored PAT:

| WP Endpoint | Forwards to | Purpose |
|---|---|---|
| `GET /wp-json/coreops/v1/events` | `GET /api/calendar-events` | Calendar feed |
| `GET /wp-json/coreops/v1/availability` | `GET /api/availability` | Availability check |
| `POST /wp-json/coreops/v1/book` | `POST /api/bookings` | Submit booking |

Each proxy endpoint:
- Validates and sanitises incoming parameters
- Attaches `Authorization: Bearer <PAT>` header
- Returns normalised JSON to the browser
- Nonce-protected POST endpoint

**Effort:** 5h

---

### WP.3 — Calendar display

Frontend calendar populated from the WP REST proxy:

- **Library:** FullCalendar.js (MIT, widely used in WP) enqueued via `wp_enqueue_script`
- Events fetched on load from `/wp-json/coreops/v1/events`
- Click on event shows detail popup (title, type, status, time)
- Configurable view (month / week / agenda) via shortcode attribute or block setting
- Responsive — works on mobile

**Effort:** 8h

---

### WP.4 — Availability search

Date-range picker that queries availability before booking:

- Start/end date inputs (flatpickr or native `<input type="date">`)
- Calls `/wp-json/coreops/v1/availability`
- Shows "Available" / "Not available — N events overlap" result
- Optional: user_id filter if booking for a specific person
- Leads directly into the booking form (WP.5) on available result

**Effort:** 5h

---

### WP.5 — Booking form

Form that submits a booking to CoreOps-Base:

- Fields: name, email, date/time, notes (configurable)
- Client-side validation before submit
- Nonce + honeypot spam protection
- Calls `POST /wp-json/coreops/v1/book` on submit
- Success/error messaging
- Optional: confirmation email triggered on CoreOps side

**Effort:** 6h

---

### WP.6 — Gutenberg block / shortcode

Package the calendar + availability + booking form as embeddable units:

- **Shortcode:** `[coreops_calendar]`, `[coreops_availability]`, `[coreops_booking]` with attribute support
- **Gutenberg block:** React-based block with InspectorControls sidebar for config (view type, user_id filter, form fields toggle)
- Block uses `@wordpress/scripts` build tooling

**Effort:** 6h

---

### WP.7 — Admin booking viewer (optional)

Read-only view of submitted bookings inside WP Admin:

- Custom admin page listing recent bookings fetched from CoreOps API
- Filterable by date range and status
- Links through to the CoreOps-Base system logs for detail

**Effort:** 5h *(optional)*

---

## Summary

| Phase | Description | Effort | Priority |
|---|---|---|---|
| WP.0 | CoreOps-Base: new API endpoints | 4h | Required |
| WP.1 | Plugin scaffold & settings | 4h | Required |
| WP.2 | WordPress REST proxy | 5h | Required |
| WP.3 | Calendar display | 8h | Required |
| WP.4 | Availability search | 5h | Required |
| WP.5 | Booking form | 6h | Required |
| WP.6 | Gutenberg block / shortcode | 6h | Required |
| WP.7 | Admin booking viewer | 5h | Optional |
| | **Core total** | **38h** | |
| | **With optional WP.7** | **43h** | |

---

## Recommended Build Order

1. WP.0 — extend CoreOps-Base API first (unblocks everything else)
2. WP.1 → WP.2 — scaffold + proxy (foundation for all frontend work)
3. WP.3 — calendar display (proves the data pipeline end-to-end)
4. WP.4 → WP.5 — availability + booking (core user journey)
5. WP.6 — package as block/shortcode (makes it embeddable anywhere)
6. WP.7 — admin viewer (add last, lowest risk)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Plugin language | PHP 8.1+ |
| WP API | Settings API, REST API (`register_rest_route`) |
| Calendar library | FullCalendar.js v6 |
| Date picker | flatpickr |
| Block editor | `@wordpress/scripts`, React, `@wordpress/blocks` |
| Build tool | `@wordpress/scripts` (webpack wrapper) |
| Auth (WP → CoreOps) | Bearer token (PAT stored in WP options) |
