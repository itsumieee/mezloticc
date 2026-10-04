# Production deployment

The checked-in Nginx and Supervisor files are templates. Replace the example hostname, certificate paths, and application path before enabling them. Keep `.env` and deployment secrets outside version control.

## Server prerequisites

- Ubuntu 24.04 with Nginx, MySQL 8, Redis, Supervisor, PHP 8.3 FPM/CLI, Composer, and Node.js 20.
- PHP extensions: `mbstring`, `pdo_mysql`, `bcmath`, `gd`, `curl`, `zip`, and `redis`.
- Create a least-privilege MySQL application user and database; do not use the MySQL root account for Laravel.

## Configure

1. Copy `.env.production.example` to `.env`, set `APP_KEY`, domain, database credentials, Redis settings, and `TRUSTED_PROXIES` if there is a trusted proxy in front of Nginx.
2. Generate an application key with `php artisan key:generate` and keep it stable across deploys.
3. Add `deploy/nginx-rate-limit.conf` inside the global Nginx `http {}` block. The `limit_req_zone` directive is invalid inside a virtual-host `server {}` block.
4. Point DNS at the server and obtain the first Let's Encrypt certificate with Certbot's Nginx plugin (or temporarily install an HTTP-only virtual host). Then install `deploy/nginx.conf`, update its host/certificate paths, and validate with `nginx -t` before reloading Nginx.
5. Install `deploy/supervisor.conf` under `/etc/supervisor/conf.d/`, then run `supervisorctl reread`, `supervisorctl update`, and `supervisorctl status`.
6. Configure Laravel's scheduler once per minute for the `www-data` user:

   `* * * * * cd /var/www/roblox-account-checker && php artisan schedule:run >> /dev/null 2>&1`

7. Ensure `storage` and `bootstrap/cache` are writable by `www-data`, then install dependencies, build assets, migrate, and warm Laravel caches.
8. Run `php artisan storage:link` if public-disk files need direct `/storage` URLs; OG SVGs themselves are served through Laravel.

## Deploy

Run `bash /var/www/roblox-account-checker/deploy/deploy.sh main` from the deployment account that owns the checkout and can reload PHP-FPM through a narrowly scoped `sudoers` rule. The script uses a fast-forward-only pull and brings the application out of maintenance mode on failure. It does not discard local working-tree changes.

Check `https://checker.example.com/health` after deploy. Queue workers use Redis and are restarted by the deploy script; Supervisor will bring them back up.