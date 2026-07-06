# NHMP 130 CRM — Production Deployment Checklist

> Apply each item before going live. Tick off as you complete them.
> **Dev environment is fine as-is. These are PRODUCTION-ONLY changes.**

---

## 1. Environment & Application Secrets

- [ ] **Set `APP_DEBUG=false`**
  ```ini
  # .env (production)
  APP_DEBUG=false
  APP_ENV=production
  LOG_LEVEL=error
  APP_NAME="NHMP 130 CRM"
  ```

- [ ] **Fix Docker Compose default** — change:
  ```yaml
  # docker-compose.yml line 27
  - APP_DEBUG=${APP_DEBUG:-false}   # was: true
  ```

- [ ] **Enable session encryption**
  ```ini
  SESSION_ENCRYPT=true
  SESSION_SAME_SITE=strict
  SESSION_SECURE_COOKIE=true      # Requires HTTPS
  SESSION_LIFETIME=30             # 30-min idle timeout for federal compliance
  ```

- [ ] **Rotate all credentials** — generate with `openssl rand -base64 32`

  | Secret | Current (dev) | Action |
  |--------|--------------|--------|
  | `DB_PASSWORD` | `crm_password` | Replace with 32-char random |
  | `DB_ROOT_PASSWORD` | `secret` | Replace with 32-char random |
  | `REDIS_PASSWORD` | *(none)* | Set to 32-char random |
  | `APP_KEY` | (already random) | Rotate via `php artisan key:generate` |

- [ ] **Update `.env` with `REDIS_PASSWORD`**
  ```ini
  REDIS_PASSWORD=<your-32-char-secret>
  REDIS_CLIENT=phpredis
  ```

---

## 2. Nginx — TLS / Security Headers / Rate Limiting

- [ ] **Enable TLS** — install certificate (Let's Encrypt for staging, org cert for prod)
  ```bash
  certbot --nginx -d crm.nhmp.gov.pk
  ```

- [ ] **Add HTTP → HTTPS redirect** to `nginx.conf`:
  ```nginx
  server {
      listen 80;
      server_name crm.nhmp.gov.pk;
      return 301 https://$host$request_uri;
  }
  server {
      listen 443 ssl http2;
      ssl_certificate     /etc/letsencrypt/live/crm.nhmp.gov.pk/fullchain.pem;
      ssl_certificate_key /etc/letsencrypt/live/crm.nhmp.gov.pk/privkey.pem;
      ssl_protocols       TLSv1.2 TLSv1.3;
      ssl_ciphers         ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
      ssl_prefer_server_ciphers on;
      ssl_session_cache   shared:SSL:10m;
      ssl_session_timeout 1d;
      ...rest of current config...
  }
  ```

- [ ] **Add security headers** inside the `server {}` block:
  ```nginx
  add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
  add_header X-Content-Type-Options    "nosniff" always;
  add_header X-Frame-Options           "DENY" always;
  add_header X-XSS-Protection          "1; mode=block" always;
  add_header Referrer-Policy           "strict-origin-when-cross-origin" always;
  add_header Permissions-Policy        "camera=(), microphone=(), geolocation=()" always;
  add_header Content-Security-Policy   "default-src 'self'; script-src 'self'; object-src 'none'; frame-ancestors 'none';" always;
  ```

- [ ] **Add rate limiting** — add before the `server {}` block:
  ```nginx
  limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
  limit_req_zone $binary_remote_addr zone=api:10m   rate=60r/m;
  ```
  Add inside the `server {}` block:
  ```nginx
  location /login {
      limit_req zone=login burst=3 nodelay;
  }
  location ~ ^/(api|spatial)/ {
      limit_req zone=api burst=20 nodelay;
  }
  ```

---

## 3. Redis — Authentication & Network Isolation

- [ ] **Remove Redis host port binding** from `docker-compose.yml`:
  ```yaml
  # REMOVE this section entirely:
  redis:
    ports:
      - "6380:6379"   # ← DELETE
  ```

- [ ] **Enable Redis password** — update `docker-compose.yml`:
  ```yaml
  redis:
    image: redis:alpine
    command: redis-server --requirepass "${REDIS_PASSWORD}" --bind 127.0.0.1
    # ports: section deleted
  ```

- [ ] **Verify Redis password is set in `.env`**:
  ```ini
  REDIS_PASSWORD=<your-32-char-secret>
  ```

---

## 4. MySQL — ACID Compliance & Network Isolation

- [ ] **Fix ACID settings** in `mysql/my.cnf`:
  ```ini
  # CHANGE these two lines:
  innodb_flush_log_at_trx_commit = 1    # was: 2 — now fully ACID
  sync_binlog                    = 1    # was: 0 — now sync on every write
  ```
  > ⚠️ This adds ~10–15% write overhead. Acceptable for emergency dispatch integrity.

- [ ] **Remove MySQL host port binding** from `docker-compose.yml`:
  ```yaml
  # REMOVE:
  db:
    ports:
      - "3309:3306"   # ← DELETE — access via: docker exec -it <container> mysql
  ```

- [ ] **Rotate MySQL root password** — update `docker-compose.yml`:
  ```yaml
  - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}    # No default fallback
  ```

---

## 5. Docker / Container Hardening

- [ ] **Remove full source mount** from `docker-compose.yml` app service:
  ```yaml
  # CHANGE volumes: from:
  - .:/var/www/html             # ← REMOVE — exposes .env, .git, configs
  # TO (explicit mounts only):
  - ./storage:/var/www/html/storage
  - ./public:/var/www/html/public
  ```
  > `.env` values should be passed as environment variables in docker-compose, not as a file mount.

- [ ] **Remove `migrate --force` from `entrypoint.sh`** or convert to a conditional:
  ```sh
  # Comment out or replace line 14 in entrypoint.sh:
  # php artisan migrate --force     ← REMOVE FROM AUTO-BOOT
  # Instead: run migrations manually once during deployment:
  # docker exec crm_app php artisan migrate --force
  ```

---

## 6. Application-Level Checks

- [ ] **Run the new migration** after deployment:
  ```bash
  docker exec crm_app php artisan migrate --force
  # Applies: 2026_05_03_000000_remove_sub_sectors_and_add_user_scopes.php
  ```

- [ ] **Verify registration route is gone** — test that `/register` returns 404:
  ```bash
  curl -I https://crm.nhmp.gov.pk/register
  # Expected: HTTP 404
  ```

- [ ] **Verify 2FA enforcement** — check `bootstrap/app.php` includes `TwoFactorEnforcement`:
  ```php
  $middleware->web(append: [\App\Http\Middleware\TwoFactorEnforcement::class]);
  ```

- [ ] **Add audit_log WORM triggers** to MySQL (run once post-migration):
  ```sql
  DELIMITER $$
  CREATE TRIGGER audit_logs_no_update
      BEFORE UPDATE ON audit_logs FOR EACH ROW
      BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs: updates not permitted'; END$$
  CREATE TRIGGER audit_logs_no_delete
      BEFORE DELETE ON audit_logs FOR EACH ROW
      BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs: deletes not permitted'; END$$
  DELIMITER ;
  ```

- [ ] **Verify API routes require auth** — test unauthenticated request returns 401:
  ```bash
  curl -I https://crm.nhmp.gov.pk/api/spatial/search?q=m2
  # Expected: HTTP 401 Unauthorized
  ```

- [ ] **Verify session is encrypted** — check Redis for session key:
  ```bash
  docker exec crm_redis redis-cli -a "$REDIS_PASSWORD" KEYS "laravel_session:*"
  # Value should be binary/encrypted, not readable JSON
  ```

- [ ] **Test brute-force lockout** (if implemented) — 5 failed logins should lock account.

- [ ] **Set cron for session cleanup**:
  ```bash
  # Add to crontab on VPS:
  * * * * * docker exec crm_app php artisan schedule:run
  ```

---

## Quick Reference — Critical `.env` diff (dev → prod)

```diff
- APP_ENV=local
+ APP_ENV=production
- APP_DEBUG=true
+ APP_DEBUG=false
- LOG_LEVEL=debug
+ LOG_LEVEL=error
- DB_PASSWORD=crm_password
+ DB_PASSWORD=<32-char-random>
- SESSION_ENCRYPT=false
+ SESSION_ENCRYPT=true
+ SESSION_SECURE_COOKIE=true
+ SESSION_SAME_SITE=strict
+ SESSION_LIFETIME=30
+ REDIS_PASSWORD=<32-char-random>
```
