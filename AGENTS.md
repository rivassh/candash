# AI Working Rules

## Production Database Protection

This project uses a shared production database. Treat the database as read-only unless you are explicitly adding a new column or table.

Do NOT run any of the following against the running production containers:

- `php artisan migrate:fresh`
- `php artisan migrate:rollback`
- `php artisan db:wipe`
- `php artisan down` / `php artisan up`
- `TRUNCATE`, `DELETE`, or direct SQL writes to data tables
- `DROP TABLE`

The only allowed write operations are:

- `php artisan migrate --force` to add new columns or tables
- `php artisan db:seed --force --class=UserSeeder` to restore default users

If you need test data, use the mock API or model factories in an isolated test database. Do not modify the shared production database.

Before running any command that may change the database, stop and ask the user for approval.
