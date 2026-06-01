# CoreOps Booking — WordPress Plugin

Displays a calendar populated from the CoreOps-Base API, allows visitors to search availability, and submit bookings. All API calls are proxied server-side — the Personal Access Token never reaches the browser.

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.4+ |
| PHP | 8.1+ |
| Node.js | 18+ |
| npm / pnpm | any recent version |
| CoreOps-Base | running instance with API enabled |

---

## Installation

### 1. Clone or copy the plugin

Place the plugin folder inside your WordPress installation:

```
wp-content/plugins/coreops-booking/
```

### 2. Install JS dependencies

```bash
cd wp-content/plugins/coreops-booking
npm install
```

### 3. Build the Gutenberg blocks

**Production build** (minified, used on live sites):
```bash
npm run build
```

**Development build** (watch mode, rebuilds on file save):
```bash
npm run start
```

The compiled output lands in `build/`. This directory is gitignored — always run `npm run build` after a fresh clone or before activating on a new server.

### 4. Activate the plugin

In the WordPress admin go to **Plugins → Installed Plugins**, find **CoreOps Booking**, and click **Activate**.

### 5. Configure the connection

Go to **Settings → CoreOps Booking** and fill in:

| Field | Description |
|---|---|
| **API Base URL** | Base URL of your CoreOps-Base instance, e.g. `https://ops.example.com` (no trailing slash) |
| **Personal Access Token** | A PAT generated in CoreOps-Base under **Profile → API Tokens** |
| **Default Calendar View** | Month grid, week, or agenda list |

Click **Test Connection** to verify before saving.

---

## Usage

### Shortcodes

Embed any component on any page or post:

```
[coreops_calendar]
[coreops_calendar view="timeGridWeek" user_id="1"]

[coreops_availability]
[coreops_availability user_id="1"]

[coreops_booking]
[coreops_booking user_id="1"]
```

| Attribute | Default | Description |
|---|---|---|
| `view` | `dayGridMonth` | FullCalendar view — `dayGridMonth`, `timeGridWeek`, or `listWeek` |
| `user_id` | *(empty)* | Filter events/availability to a specific CoreOps user ID |

### Gutenberg blocks

In the block editor search for **CoreOps** — three blocks are available:

- **CoreOps Calendar** — full calendar view
- **CoreOps Availability** — date-range availability checker
- **CoreOps Booking Form** — booking submission form

Each block has an **Inspector Controls** sidebar for the same options as the shortcode attributes.

---

## Project Structure

```
coreops-booking/
├── coreops-booking.php          # Plugin entry point (WP header + bootstrap)
├── uninstall.php                # Removes wp_options on plugin deletion
├── package.json                 # @wordpress/scripts build config
│
├── includes/
│   ├── class-plugin.php         # Singleton — wires all hooks, shortcodes, blocks
│   ├── class-admin.php          # Settings page (Settings → CoreOps Booking)
│   ├── class-api-client.php     # HTTP client — calls CoreOps-Base with PAT
│   └── class-rest-api.php       # WP REST proxy (/wp-json/coreops/v1/*)
│
├── admin/
│   └── views/settings.php       # Settings page HTML template
│
├── public/
│   ├── js/calendar.js           # FullCalendar init + availability + booking JS
│   └── views/
│       ├── calendar.php         # [coreops_calendar] HTML output
│       ├── availability.php     # [coreops_availability] HTML output
│       └── booking-form.php     # [coreops_booking] HTML output
│
├── src/
│   ├── index.js                 # Webpack entry — imports all three blocks
│   └── blocks/
│       ├── calendar/            # block.json + Gutenberg edit component
│       ├── availability/
│       └── booking-form/
│
├── build/                       # Compiled JS output (gitignored — run npm run build)
├── docs/
│   └── effort-breakdown.md      # Phase planning and hour estimates
└── README.md
```

---

## WP REST Proxy Endpoints

The plugin exposes three server-side proxy routes. The browser calls these — never CoreOps directly.

| Method | Endpoint | Forwards to | Auth |
|---|---|---|---|
| `GET` | `/wp-json/coreops/v1/events` | `GET /api/calendar-events` | Public |
| `GET` | `/wp-json/coreops/v1/availability` | `GET /api/availability` | Public |
| `POST` | `/wp-json/coreops/v1/book` | `POST /api/bookings` | WP nonce |

---

## Development

### Linting

```bash
npm run lint:js
```

### Adding a new block

1. Create `src/blocks/<name>/block.json` and `src/blocks/<name>/index.js`
2. Import it in `src/index.js`
3. Run `npm run build`
4. Add a corresponding view template in `public/views/<name>.php`
5. Register a shortcode handler in `includes/class-plugin.php`

---

## CoreOps-Base API Requirements

The following endpoints must exist on the CoreOps-Base instance. See `docs/effort-breakdown.md` (WP.0) for implementation detail.

| Endpoint | Status |
|---|---|
| `GET /api/availability` | ✅ Exists |
| `GET /api/calendar-events` | 🔲 To be added (WP.0) |
| `POST /api/bookings` | 🔲 To be added (WP.0) |
