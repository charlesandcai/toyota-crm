# Toyota Silang CRM & Sales Management System

A production-ready, database-driven CRM and Sales Management Web Application for Toyota automotive sales professionals.

## Features

- **Dashboard** — Action-oriented: "What should I work on today?"
- **Lead Management** — Full CRUD, search, filters, pagination, quick filters
- **Lead Detail** — Contact info, vehicle interest, sales info, follow-up tracking, activity timeline
- **Pipeline (Kanban)** — Drag-and-drop stage movement, visual pipeline
- **Activities** — Call, message, meeting, test drive, quote, financing, follow-up tracking
- **Reports** — Monthly summary, lead performance, sales performance, follow-up performance
- **Settings** — Configurable statuses, stages, priorities, sources, models, colors, targets, working calendar
- **Data Import** — CSV upload with column mapping, validation, and preview
- **CSV Export** — Export filtered lead data
- **Sales Targets** — Monthly targets, closing ratio, leads needed estimation
- **Lead Generation** — Per-source targets, actual vs target tracking
- **Needs Attention** — Overdue follow-ups, due today, release watch, warm/hot leads
- **Mobile Responsive** — Optimized for phone use with touch-friendly UI

## Technology Stack

### Frontend
- HTML5, CSS3, Bootstrap 5
- Vanilla JavaScript (no frameworks)
- Bootstrap Icons
- Chart.js (where applicable)
- Fetch API for AJAX

### Backend
- PHP 8.2+
- Object-Oriented PHP
- PDO with prepared statements
- MySQL 8+

## Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Required PHP extensions: PDO, PDO_MySQL, mbstring, json
- XAMPP, MAMP, Laragon, or similar PHP/MySQL environment

## Installation

### 1. Clone / Copy Files

Copy the project files to your web server's document root:

```bash
# For XAMPP on macOS
cp -r crm-php /Applications/XAMPP/xamppfiles/htdocs/

# For general web servers
cp -r crm-php /var/www/html/
```

### 2. Create Database

```sql
CREATE DATABASE toyota_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import Schema

```bash
mysql -u root -p toyota_crm < database/schema.sql
```

### 4. Import Seed Data

```bash
mysql -u root -p toyota_crm < database/seed.sql
```

### 5. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=toyota_crm
DB_USER=root
DB_PASSWORD=your_password
```

### 6. Access the Application

```
http://localhost/crm-php/public/
```

## Default Login

| Username | Password |
|----------|----------|
| admin    | admin123 |

> **IMPORTANT:** Change the default password after first login.

## Project Structure

```
crm-php/
├── config/
│   ├── app.php              # Environment config loader
│   ├── bootstrap.php        # Application bootstrap
│   └── database.php         # Database connection
├── app/
│   ├── controllers/         # Request handlers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── LeadController.php
│   │   ├── ActivityController.php
│   │   ├── PipelineController.php
│   │   ├── ReportController.php
│   │   ├── SettingsController.php
│   │   ├── ImportController.php
│   │   └── ApiController.php
│   ├── models/              # Data access
│   │   ├── Model.php        # Base model
│   │   ├── LeadModel.php
│   │   ├── UserModel.php
│   │   ├── SettingsModel.php
│   │   └── ActivityModel.php
│   ├── services/            # Business logic
│   │   ├── FollowUpService.php
│   │   ├── SalesMetricsService.php
│   │   ├── WarmLeadService.php
│   │   └── WorkingDaysService.php
│   └── helpers/             # Utilities
│       ├── Router.php
│       ├── Security.php
│       ├── Url.php
│       ├── Validation.php
│       └── Response.php
├── database/
│   ├── schema.sql           # Database structure
│   └── seed.sql             # Default data + demo leads
├── public/
│   ├── index.php            # Entry point
│   └── assets/
│       ├── css/app.css
│       └── js/app.js
├── views/
│   ├── layouts/             # Main layout + sidebar
│   ├── auth/                # Login
│   ├── dashboard/           # Dashboard
│   ├── leads/               # Lead CRUD
│   ├── pipeline/            # Kanban pipeline
│   ├── activities/          # Activity log
│   ├── reports/             # Reports
│   ├── settings/            # Configuration
│   └── imports/             # Data import
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes
├── .env.example
├── .env
└── README.md
```

## Database Architecture

### Tables

| Table | Purpose |
|-------|---------|
| `users` | System users (admin/sales) |
| `leads` | Core CRM records |
| `activities` | Activity history per lead |
| `lead_statuses` | Configurable status values |
| `opportunity_stages` | Pipeline stages |
| `priorities` | Priority levels |
| `lead_sources` | Lead source tracking |
| `vehicle_models` | Toyota models |
| `vehicle_colors` | Vehicle colors |
| `sales_targets` | Monthly sales targets |
| `lead_generation_targets` | Per-source monthly targets |
| `settings` | Key-value configuration |
| `working_days` | Working day configuration |
| `holidays` | Holiday/non-working dates |

### Key Design Decisions

- **Leads table is the source of truth** — Pipeline views filter leads by opportunity stage
- **Soft deletion** — Leads are archived, not deleted
- **Foreign keys** — Referential integrity enforced at database level
- **Deactivated references** — Statuses/stages/sources use `active` flag, not deletion
- **Dynamic calculations** — Follow-up status, days since contact calculated at runtime

## Configuration Options

### Settings Page

- **Lead Statuses** — Add, edit, reorder, deactivate
- **Opportunity Stages** — Add, edit, reorder, deactivate
- **Priority Levels** — Add, edit, deactivate
- **Lead Sources** — Add, edit, deactivate
- **Vehicle Models** — Add, edit, deactivate
- **Vehicle Colors** — Add, edit, deactivate
- **Sales Targets** — Monthly targets by year
- **Lead Generation Targets** — Per-source monthly targets
- **Working Days** — Toggle working days (Mon-Fri default)
- **Holidays** — Add/remove non-working dates

### Database Settings

| Key | Description |
|-----|-------------|
| `closed_release_stage` | Stage that counts as "closed" (default: Released) |
| `closing_ratio_method` | Method description for UI |
| `app_name` | Application name |
| `app_timezone` | Timezone (default: Asia/Manila) |

## Security

- PDO prepared statements for all queries
- CSRF protection on all forms
- XSS prevention via output escaping
- Password hashing with `password_hash()` / `password_verify()`
- Session-based authentication
- Input validation on server and client side
- File upload validation (MIME type, size limits)
- No hardcoded credentials

## Timezone

All dates and times use the configured timezone (`Asia/Manila` by default). Server-side calculation ensures consistency.

## Limitations & Future Improvements

1. **XLSX Import** — Requires PhpSpreadsheet library installation
2. **Multi-user** — Architecture supports it; would need role-based access control
3. **Audit Log** — Partial implementation; could be expanded
4. **Email Integration** — Not yet implemented
5. **SMS Integration** — Not yet implemented
6. **Dashboard Charts** — Chart.js is loaded but could add more visualizations
7. **PDF Export** — Not yet implemented
8. **Two-factor Authentication** — Not yet implemented
9. **Bulk Actions** — Could add bulk status/stage updates
10. **Custom Fields** — Could add configurable custom lead fields

## License

This project is provided as-is for Toyota Silang CRM use.
