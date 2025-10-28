# HIMS — Dr Romel Cruz Hospital

This repository contains the Hospital Information Management System (HIMS) used by Dr Romel Cruz Hospital. It's a Laravel-based application implementing patient records, admissions, billing, lab orders and basic reporting.

## Quick overview

- Framework: Laravel (Blade views, Eloquent, Artisan)
- Language: PHP (8+)
- Front-end: Blade + minimal JS, Vite for asset bundling
- Database: MySQL (or MariaDB)

## Prerequisites (Windows / XAMPP)

- PHP 8.0+
- Composer
- Node.js (14+)
- npm (or yarn)
- MySQL (bundled with XAMPP)
- Optional: Git, VS Code

If you're developing on Windows with XAMPP, use the XAMPP Control Panel to start Apache and MySQL. Point your virtual host / DocumentRoot to the project's `public/` folder, or use the built-in PHP server for quick testing.

## Quick setup (development)

Open PowerShell and run the following commands from the project root (`d:\xampp\htdocs\DrRomelCruzHP`):

```powershell
# Install PHP dependencies
composer install --no-interaction --prefer-dist

# Install JS dependencies and build assets (development)
npm install
npm run dev

# Copy env and generate app key
copy .env.example .env
php artisan key:generate

# Configure .env: set DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD to match your MySQL/XAMPP settings

# Run migrations (and seeders if desired)
php artisan migrate --seed

# Create storage symlink used by the app
php artisan storage:link

# Clear compiled views/cache when making view/controller changes
php artisan view:clear; php artisan cache:clear; php artisan config:clear

# Run the development server (optional)
php artisan serve --host=127.0.0.1 --port=8000
# Then open http://127.0.0.1:8000 in your browser
```

If you prefer to use Apache from XAMPP, point your virtual host to the `public/` folder and ensure PHP and Composer are on your PATH or use the XAMPP-provided PHP binary.

## Running tests

Run the application's tests with PHPUnit or the artisan wrapper:

```powershell
php artisan test
# or
vendor\\bin\\phpunit
```

## Helpful Artisan commands

- `php artisan view:clear` — clear compiled Blade templates
- `php artisan cache:clear` — clear application cache
- `php artisan config:clear` — clear config cache
- `php artisan migrate` — run database migrations

## Admin pages & reporting

- Patients management: `/admin/patients`
	- Use the filters and search box to find patients.
	- The `View Admitted Patients` / Reports UI uses the `filter=admitted` parameter.

- Printable admitted patients report:
	- Open `/admin/patients?filter=admitted&period=this_month` to view the admitted list for the selected period.
	- To trigger the print-optimized layout automatically, add `&print=1`.
	- Supported `period` values: `past_year`, `past_month`, `past_week`, `this_year`, `this_month`, `this_week`.
	- You may also supply `date_from` and `date_to` (format `YYYY-MM-DD`) for a custom range.

Examples:

```
/admin/patients?filter=admitted&period=past_month
/admin/patients?filter=admitted&period=this_month&print=1
/admin/patients?filter=admitted&date_from=2025-01-01&date_to=2025-01-31&print=1
```

## Notes about recent changes (developer)

- The FHIR transformation service (`app/Services/FHIR/FhirService.php`) contains backward-compatibility wrappers to avoid runtime errors in older code paths.
- The admin FHIR UI was cleaned to remove hard-coded production URLs.
- The admissions printable report groups admissions by patient and supports calendar-based `past_*` and `this_*` period filters.
- The patients list can highlight rows that correspond to admissions in the selected period (server-side computed `highlightPatientIds`).

## Troubleshooting

- If views are not updating after editing Blade files, run:

```powershell
php artisan view:clear; php artisan cache:clear
```

- If you encounter database errors during migration, double-check your `.env` DB settings and ensure the MySQL service is running in XAMPP.

## Contributing

If you make changes, please:

- Run `composer install` and `npm install` to update dependencies locally.
- Run `php artisan migrate` and `php artisan migrate:refresh --seed` when schema changes are introduced (coordinate with the team).
- Keep migrations and seeders up to date.

## License

This project follows the licensing indicated in the repository. See the `composer.json` for package licenses.

---

If you want, I can also:
- Add a short `CONTRIBUTING.md` with local dev notes and conventions,
- Add a small `docker-compose` for reproducible development, or
- Update the README with screenshots for the Reports/Print UI.
