# SH05 Smart Attendance

Web application for personal attendance, leave requests, permission requests, schedules, and profile details. The original HTML interface has been migrated to Laravel Blade views, and the available data-entry flows now persist to MySQL.

## Stack and requirements

- Laravel 13
- PHP 8.3 or later with PDO MySQL, Fileinfo, GD, and the Laravel-required PHP extensions
- Composer 2
- MySQL 8 or compatible MariaDB
- Node.js and npm (only needed when changing/building Vite-managed assets; the migrated pages currently keep Tailwind CSS and Chart.js from their existing CDNs)

## Install

1. Install PHP 8.3+, Composer, and MySQL. Create a database named `sh05_smart_attendance` with `utf8mb4` encoding.
2. Install PHP packages:

   ```sh
   composer install
   ```

3. Copy `.env.example` to `.env`, set `APP_URL`, and set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` for your local MySQL account.
4. Generate an application key and create tables:

   ```sh
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   ```

5. Start the development server:

   ```sh
   php artisan serve
   ```

Open the URL shown by Artisan. Create an account from the Register page. The seeder does not add fake attendance or demo user records; registration stores a password using Laravel's hashed password cast.

## Test

Tests use a separate MySQL database named `sh05_smart_attendance_test`. Create it before running:

```sh
php artisan test
```

The test suite migrates this test database. Never point `phpunit.xml` at a database containing application data.

## Main structure

- `app/Http/Controllers` — authentication, attendance, requests, schedules, and profile handlers
- `app/Models` — users and attendance/request/schedule records
- `database/migrations` — MySQL schema
- `resources/views` — Blade pages migrated from the original HTML pages
- `public/js` and `public/assets` — browser scripts and the LEXA logo
- `routes/web.php` — named web routes; the attendance submission uses the authenticated session and CSRF protection, so a separate API route is not required

## Current behavior

- Registration and login gate the application pages; users can only read and submit their own attendance and request records.
- Leave, permission, and schedule forms validate on the server and save records linked to the signed-in user.
- Check-in/out sends the browser's location and camera image to Laravel and stores each event's time and image path on Laravel's non-public local disk.
- Profile details and an optional profile image can be saved.
- Attendance calendar, attendance log, request lists, and schedule list read from MySQL.
- Existing dashboard/report demo summaries remain presentation-only until the attendance policy (workdays, lateness, absences, and approval roles) is defined. They are not used as database records.

There is no admin/approval role in the original interface, so requests start as `pending` and only their owner can view them. Approval workflow, employee directory, and organization-wide reports need requirements before implementation.

## Attendance location and network validation

Attendance check-in and check-out require both a GPS position within `ATTENDANCE_RADIUS` meters of the office and a request IP that exactly matches `OFFICE_ALLOWED_IP`. Laravel recalculates the distance with the submitted latitude and longitude, checks the browser-reported GPS accuracy against `ATTENDANCE_MAX_ACCURACY`, and stores the accepted coordinates, measured distance, accuracy, and request IP on the existing attendance record.

Set these values in `.env` before taking attendance:

```dotenv
OFFICE_LATITUDE=
OFFICE_LONGITUDE=
ATTENDANCE_RADIUS=50
GPS_MAX_ACCURACY=200
OFFICE_ALLOWED_IP=
ATTENDANCE_LOCAL_ALLOW_WITHOUT_OFFICE_IP=false
```

Enter the actual office coordinates. `OFFICE_ALLOWED_IP` must be the public IP observed by the Laravel server. GPS accuracy is displayed and warned about when it exceeds `GPS_MAX_ACCURACY`; it does not reject attendance. The backend still recalculates distance and rejects positions beyond `ATTENDANCE_RADIUS`.

On Laragon, Laravel usually sees `127.0.0.1`, `::1`, or a private LAN address instead of the router's public IP. In `APP_ENV=local`, the attendance page displays `Request::ip()`, `REMOTE_ADDR`, `X-Forwarded-For`, and the configured office IP. `X-Forwarded-For` is diagnostic only unless it comes through a specifically trusted proxy. To test attendance locally without claiming that a private IP is the office public IP, set `ATTENDANCE_LOCAL_ALLOW_WITHOUT_OFFICE_IP=true`; the page reports `DEVELOPMENT / NOT CHECKED`. This opt-in is honored only in the local environment and is ignored in production. Keep it `false` in production. Production requires an exact match with `OFFICE_ALLOWED_IP`.

Browser geolocation requires a secure context (HTTPS), except for localhost. The backend never trusts a client-supplied distance, but browser coordinates and reported accuracy still originate from the client and can be spoofed; a web application cannot independently prove GPS hardware location.
