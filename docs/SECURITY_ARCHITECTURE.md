# NHMP 130 CRM — Security Architecture & Access Control Reference

> Developer reference. Last updated: 2026-04-19

---

## 1. Authentication & Session Flow

```
Browser → POST /login
    → LoginRequest::authenticate()
        ├─ Check user.is_active (hard block)
        ├─ Check user.locked_until (time-based lockout)
        ├─ Auth::attempt()
        │    ├─ FAIL → increment failed_attempts → lock at 5 strikes (30 min)
        │    │          write audit_logs{action: login_failed}
        │    └─ SUCCESS → clear failed_attempts, locked_until
        │                 write login_logs{login_at, ip, user_agent}
        └─ TwoFactorEnforcement middleware
             ├─ user.totp_secret not set → OK (2FA not yet enrolled)
             └─ not session('2fa_verified') → redirect to GET /2fa
```

**Session config (production):**
```ini
SESSION_DRIVER=redis       # Stored in Redis
SESSION_ENCRYPT=true       # Encrypted at rest
SESSION_LIFETIME=30        # 30-min idle timeout
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

---

## 2. Authorization Layers

### Layer 1 — Route Middleware
```php
// Requires authenticated user
Route::middleware(['auth'])

// Requires specific permission via hasPermission()
Route::middleware(['can:users.manage'])
Route::middleware(['can:reports.view'])
Route::middleware(['can:administration.full'])
```

### Layer 2 — Controller Gate
```php
// In every mutating controller method:
$this->authorize('permission.name');
```

### Layer 3 — Self-Protection Guards
```php
// Prevents admin from nuking their own account:
abort_if($user->id === auth()->id(), 403, ...);
```

### Layer 4 — Data Scope Visibility
All call data queries pass through `CallController::applyScopeVisibility()`:
```
Super Admin / Supervisor → sees ALL calls
Agent                    → sees only own calls (agent_id = auth()->id())
Scoped users             → UNION of all active UserScopes (OR WHERE)
No scopes assigned       → WHERE 1=0 (zero rows — safe default)
```

---

## 3. Multi-Scope Access Control System

### Schema
```sql
user_scopes (
    user_id       FK → users.id  CASCADE DELETE
    unit_type     ENUM(national, zone, sector, beat, call_centre)
    unit_id       BIGINT NULLABLE  -- NULL = all of that type
    access_level  ENUM(read_only, read_write, full)
    label         VARCHAR(100)     -- e.g. "South Zone — View Only"
    is_active     BOOLEAN
)
```

### Examples
| User | Scopes | Effect |
|------|--------|--------|
| Zone Admin | zone/id=3/full | Sees all calls in Zone 3, can CUD |
| Dual-scope | zone/id=3/read_only + call_centre/null/full | Call log view in zone 3 + full CUD in call center |
| National | national/null/full | Sees everything |
| No scopes | (none) | Sees nothing |

### API Endpoints
```
GET    /admin/users/{user}/scopes          List all scopes
POST   /admin/users/{user}/scopes          Add a scope
PATCH  /admin/users/{user}/scopes/{scope}  Update access_level/label
DELETE /admin/users/{user}/scopes/{scope}  Remove a scope
```

---

## 4. Role Hierarchy

| Constant | Value | Typical Scope |
|----------|-------|--------------|
| `Role::SUPER_ADMIN` | `super_admin` | National/full — bypasses all scope filters |
| `Role::IT_ADMIN` | `operation_admin` | National/read-write |
| `Role::ZONE_ADMIN` | `zone_admin` | Zone-level |
| `Role::SECTOR_ADMIN` | `sector_admin` | Sector-level |
| `Role::BEAT_OPERATOR` | `beat_operator` | Beat-level |
| `Role::AGENT_SUPERVISOR` | `agent_supervisor` | All calls (supervisor visibility) |
| `Role::AGENT` | `agent` | Own calls only |

Use constants, never raw strings:
```php
// ✅ Correct
if ($user->role?->name === Role::SUPER_ADMIN) ...

// ❌ Wrong — breaks silently if DB value renamed
if ($user->role?->name === 'super_admin') ...
```

---

## 5. Audit & Forensics

### Tables
| Table | Purpose | WORM? |
|-------|---------|-------|
| `audit_logs` | Application-level events (Spatie + custom) | ✅ Trigger-enforced |
| `login_logs` | Login/logout with IP + user_agent + timestamps | ❌ Soft-protected |
| `password_histories` | Last N hashed passwords per user | ❌ |
| `call_status_history` | Every status transition on a call | ❌ |

### Querying Failed Logins
```sql
SELECT u.username, al.ip_address, al.created_at
FROM audit_logs al
JOIN users u ON u.id = al.user_id
WHERE al.action = 'login_failed'
  AND al.created_at > NOW() - INTERVAL 24 HOUR
ORDER BY al.created_at DESC;
```

### audit_logs Partitions
Table is partitioned by `TO_DAYS(created_at)` (monthly slices).
- Query efficiency: add `WHERE created_at BETWEEN '2026-04-01' AND '2026-04-30'`
- Archive old data: `ALTER TABLE audit_logs DROP PARTITION p202601`
- Add next month: run `php artisan audit:add-partition`

---

## 6. Password Policy

- **Minimum:** Laravel `Password::defaults()` (8+ chars, mixed case)
- **History:** Last 5 passwords rejected on change
- **Lockout:** 5 consecutive failures → 30-minute lockout (`users.locked_until`)
- **IP throttle:** Laravel `RateLimiter` 5 attempts/minute per IP+username key
- **Reset:** Admin can reset via `/admin/users/{user}/reset-password`

---

## 7. Key Files Reference

| File | Purpose |
|------|---------|
| `app/Http/Middleware/TwoFactorEnforcement.php` | 2FA redirect middleware (registered in bootstrap/app.php) |
| `app/Http/Requests/Auth/LoginRequest.php` | Login validation + brute-force lockout |
| `app/Models/UserScope.php` | Multi-scope access model |
| `app/Models/Role.php` | Role constants (SUPER_ADMIN, AGENT, etc.) |
| `app/Http/Controllers/UserController.php` | User CRUD + scope CRUD AJAX endpoints |
| `app/Http/Controllers/CallController.php` | `applyScopeVisibility()` — core data isolation |
| `database/migrations/2026_05_03_*` | user_scopes table + drop sub_sectors |
| `database/migrations/2026_05_04_*` | FK constraints + WORM triggers + languages table |
| `database/migrations/2026_05_05_*` | audit_logs monthly partitioning |
| `docs/PRODUCTION_DEPLOYMENT_CHECKLIST.md` | Pre-launch security checklist |
