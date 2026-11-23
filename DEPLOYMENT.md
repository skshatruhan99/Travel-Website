# Deployment Checklist for NpAdvanture

## 1. Production environment setup

### Required environment variables

Copy `.env.example` to `.env` and update the values:

- `APP_NAME` = your website name
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://yourdomain.com`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=npadvanture`
- `DB_USERNAME=npadvanture_app`
- `DB_PASSWORD=your_secure_password`
- `SESSION_SECURE_COOKIE=true`
- `MAIL_MAILER=smtp`
- `MAIL_SCHEME=smtp`
- `MAIL_HOST=smtp.gmail.com`
- `MAIL_PORT=587`
- `MAIL_USERNAME=your-full-gmail-address@gmail.com`
- `MAIL_PASSWORD=your-google-app-password`
- `MAIL_FROM_ADDRESS="your-full-gmail-address@gmail.com"`
- `MAIL_FROM_NAME="NpAdvanture"`
- `QUEUE_CONNECTION=database`
- `ESEWA_MODE=production`
- `ESEWA_PRODUCT_CODE=your-product-code`
- `ESEWA_SECRET_KEY=your-secret-key`
- `ESEWA_STATUS_URL=https://esewa.com.np/api/epay/transaction/status/` (optional; this is the production default)

## 2. Laravel production setup

Before deployment, create a new production `.env` from the hosting secret store. Generate `APP_KEY` once for a new installation only; preserve it across deployments and backups.

Do not run `php artisan db:seed` on production: the default seeder intentionally seeds demo catalog data only in local and testing environments. Create the first production administrator through a controlled, one-time process instead.

### Database backup before migrations

Take a database backup before running migrations or deploying schema changes. Keep backups outside the web root, restrict file access, encrypt off-server copies, and test restoring a backup before launch:

```bash
umask 077
backup_file="$BACKUP_DIR/npadvanture-$(date +%Y%m%d-%H%M%S).sql"
mysqldump --single-transaction --routines --triggers \
  --user="$DB_USERNAME" --password --host="$DB_HOST" \
  --databases "$DB_DATABASE" --result-file="$backup_file"
```

The bare `--password` option prompts for the database password instead of putting it in shell history or the process arguments.

Run the following commands on the server:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan about
```

Run `php artisan key:generate` only on a new installation with no existing `APP_KEY`. Never replace a production key during a routine deployment; doing so can invalidate sessions and make previously encrypted data unreadable.

## 3. Web server setup

### Recommended hosting
- Hostinger / cPanel VPS
- DigitalOcean App Platform
- Laravel Forge
- Plesk / shared hosting with PHP 8.2+

### Nginx / Apache note
- point document root to `public/`
- set PHP-FPM to PHP 8.2+
- enable HTTPS with a valid certificate (for example, Let's Encrypt), renew it automatically, and redirect HTTP to HTTPS
- configure the web server/PHP-FPM to trust forwarded HTTPS headers only from the known reverse proxy, if a proxy is used
- grant the PHP process write access only to `storage/` and `bootstrap/cache/`; do not make the project root or `.env` publicly accessible

## 4. Payment and email setup

- set real eSewa credentials before going live; successful browser returns are also checked against eSewa's server-side transaction-status endpoint
- local/demo configuration uses eSewa's sandbox mode, `EPAYTEST`, and the published sandbox test secret in `.env.example`; never use sandbox values in production
- for live payments, set `ESEWA_MODE=production`, then replace the product code and secret with the merchant credentials issued by eSewa; the production payment and status endpoints are selected automatically unless `ESEWA_STATUS_URL` is explicitly overridden
- keep eSewa credentials only in the production `.env` or hosting secret manager; never commit merchant credentials
- never enable demo payments in production; only eSewa payments verified against the live server-status endpoint can mark a booking paid
- after changing eSewa settings, rebuild Laravel's cached configuration with `php artisan config:cache`
- test demo payments against the sandbox before launch; use only eSewa-issued live credentials for production transactions
- the checked-in `.env.example` uses `MAIL_MAILER=log` for local development; production must override it with a real mail provider
- for Gmail / Google Workspace SMTP, enable 2-Step Verification and create a Google App Password; use the full mailbox address as `MAIL_USERNAME` and the app password (not the account password) as `MAIL_PASSWORD`
- use `smtp.gmail.com` on port `587` with `MAIL_SCHEME=smtp` for STARTTLS; set `MAIL_FROM_ADDRESS` to that mailbox or an authorized send-as address
- keep mail credentials only in the production `.env` or hosting secret manager; never commit them
- after changing production mail settings, rebuild Laravel's cached configuration with `php artisan config:cache`
- verify the complete password-reset flow by requesting a reset for a real account, following the emailed link, setting a new password, and signing in with it
- keep a queue worker running so invoice PDFs and booking emails are generated and delivered:

```bash
php artisan queue:work --tries=3 --timeout=90
```

- create departure dates and seat capacities for each published tour from Admin → Tours → Dates; pending seats are held for 20 minutes
- paid cancellations create refund requests; process the refund in the payment provider first, then record its provider reference in Admin → Bookings
- test successful, failed, delayed, and duplicate payment returns before launch
- confirm the production session cookie has the `Secure`, `HttpOnly`, and `SameSite=Lax` attributes over HTTPS

## 5. Final checks before launch

- login works
- admin setup works only locally when needed
- user/admin role checks work
- booking form saves valid totals with discount
- coupon validation works
- invoice pages render correctly and downloadable PDFs are generated
- confirmation emails include the booking details and invoice PDF
- available tour departures show correct remaining capacity
- destination search returns live destination, tour, hotel, activity, and flight results for the selected date and party size
- authenticated users can save private trip plans and manage day-by-day catalog itineraries
- visitors can subscribe to the newsletter and admins can manage active and inactive subscribers
- cancellation and manual refund recording are tested end-to-end
- SEO tags and branding are set from admin settings
- pages are responsive on mobile

## 6. Recommended live launch sequence

1. Upload app files
2. configure `.env`
3. create MySQL database
4. run migrations and seeders
5. set file permissions
6. test admin login
7. add first admin account
8. publish real tours, hotels, and destinations
9. start live payment testing
10. launch

## 7. Production security checklist

- disable debug mode
- use a dedicated least-privilege database account (not `root`) and strong database credentials
- keep `.env` outside public access
- keep `APP_KEY`, SMTP credentials, database credentials, and eSewa merchant secrets in the hosting secret store; never commit them
- use HTTPS only
- set `APP_ENV=production`
- set `APP_DEBUG=false`
- verify `SESSION_SECURE_COOKIE=true`
- keep backup schedule for database
- perform and restore-test an encrypted off-site backup
- restrict admin access to trusted accounts

## 8. Quick admin usage

- log in as first admin via local setup route in local development
- create a real admin account from the dashboard
- open Settings and set site branding
- add destinations / tours / hotels from the admin sidebar
- configure hotel room types, nightly rates, guest capacity, and inventory from each hotel's Rooms action
- search available flights and reserve seats with passenger details through checkout
- browse activities and reserve places for a selected date through checkout
- create coupons and review tour, hotel, and flight bookings
