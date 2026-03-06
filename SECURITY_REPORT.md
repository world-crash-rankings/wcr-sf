# Security Scan Report — World Crash Rankings (WCR-SF)

**Date**: 2026-03-06
**Scope**: Full codebase static analysis (Symfony 8.0 / PHP 8.4)
**Auditor**: Automated security scan

---

## Summary

| Severity | Count |
|----------|-------|
| Critical | 1     |
| High     | 4     |
| Medium   | 4     |
| Low      | 3     |
| **Total**| **12**|

No CVEs found in Composer dependencies (`composer audit` returned clean).

---

## Critical

### [C1] Stored XSS — Unfiltered `|raw` output in Twig templates
**OWASP**: A03:2021 – Injection
**Files**:
- `templates/home/index.html.twig:66` → `{{ new.content|raw }}`
- `templates/zone/strats.html.twig:53` → `{{ strat.description|raw }}`
- `templates/zone/info.html.twig:160` → `{{ zone.description|raw }}`

**Description**: Three templates render admin-entered rich-text content with the `|raw` filter, bypassing Twig's automatic HTML escaping. Any admin who can create/edit News, Strats, or Zones can inject arbitrary JavaScript that executes in any visitor's browser (stored XSS).

**Recommendation**:
- Install an HTML sanitisation library (e.g. `tgalopin/html-sanitizer-bundle`) and sanitise content **on write** (before persisting) or use a dedicated Twig filter.
- Alternatively, define a strict allowlist of safe HTML tags in the sanitiser and apply it before calling `|raw`.
- Add a `Content-Security-Policy` header as a defence-in-depth layer (see [M3]).

---

## High

### [H1] Sensitive system information exposed via `/health/detailed` and `/health/metrics`
**OWASP**: A01:2021 – Broken Access Control / Information Disclosure
**File**: `src/Controller/HealthCheckController.php`

The `/health/detailed` endpoint (ROLE_ADMIN) returns the **database driver, host, and database name** (`checkDatabase()`, lines ~163-167). The `/health/metrics` endpoint (ROLE_SUPER_ADMIN) returns:
- All loaded PHP extensions (`get_loaded_extensions()`)
- PHP INI settings (memory limit, max execution time, upload limits)
- **System hostname** (`gethostname()`)
- **System load average** (`sys_getloadavg()`)
- Full filesystem paths

Additionally, the **public** `/health` endpoint returns the `environment` value (`dev`/`prod`), aiding attacker reconnaissance.

**Recommendation**:
- Remove database connection parameters (host, dbname, driver) from health check output; replace with a generic `"connected": true`.
- Remove PHP extension list, INI values, hostname, and load average from the metrics endpoint.
- Strip the `environment` key from the public `/health` response.
- Keep only the minimum information necessary to verify liveness/readiness.

---

### [H2] Exception messages leaked to end users
**OWASP**: A01:2021 – Information Disclosure
**File**: `src/Controller/Admin/ScoreController.php:117,155,176`

```php
$this->addFlash('error', 'Error adding score: ' . $e->getMessage());
$this->addFlash('error', 'Error updating score: ' . $e->getMessage());
$this->addFlash('error', 'Error deleting score: ' . $e->getMessage());
```

Raw exception messages (which may contain table names, column names, SQL fragments, or filesystem paths) are rendered directly in the UI.

**Recommendation**:
- Log the full exception server-side (`$this->logger->error(...)`) and show only a generic message to users.
- In production, never expose stack traces or database error details via the UI.

---

### [H3] Shell command execution via `exec()` without input sanitisation
**OWASP**: A03:2021 – Injection
**File**: `src/Controller/HealthCheckController.php` — `getAppVersion()`

```php
$version = exec('git describe --tags --always 2>/dev/null');
```

While `kernel.project_dir` is not currently user-controlled, using `exec()` is a risky pattern. If the project directory were ever derived from untrusted input, this would become a command injection.

**Recommendation**:
- Replace with Symfony's `Process` component, which does not invoke a shell:
  ```php
  $process = new Process(['git', 'describe', '--tags', '--always']);
  $process->setWorkingDirectory($projectDir);
  $process->run();
  $version = trim($process->getOutput());
  ```

---

### [H4] Silent CSRF failure on admin delete operations
**OWASP**: A01:2021 – Broken Access Control
**Files**:
- `src/Controller/Admin/ScoreController.php`
- `src/Controller/Admin/UserController.php`
- `src/Controller/Admin/CountryController.php`
- `src/Controller/Admin/NewsController.php`
- `src/Controller/Admin/StratController.php`

Pattern:
```php
if ($this->isCsrfTokenValid('delete' . $entity->getId(), $token)) {
    // perform delete
}
// silently redirects regardless of validation result
return $this->redirectToRoute('...');
```

When the CSRF token is invalid (e.g. replayed request), the deletion is silently skipped with no error feedback. Users cannot distinguish a successful deletion from an intercepted/invalid request.

**Recommendation**:
- Add an error flash and abort early on CSRF failure:
  ```php
  if (!$this->isCsrfTokenValid('delete' . $entity->getId(), $token)) {
      $this->addFlash('error', 'Invalid CSRF token.');
      return $this->redirectToRoute('...');
  }
  ```

---

## Medium

### [M1] Weak password minimum length (6 characters)
**OWASP**: A07:2021 – Identification and Authentication Failures
**File**: `src/Form/UserType.php:51`

```php
new Assert\Length(min: 6),
```

NIST SP 800-63B recommends a minimum of **8 characters** (12+ is better practice for admin accounts).

**Recommendation**:
- Increase minimum to **12 characters** for admin accounts.
- Consider adding `Assert\PasswordStrength` (Symfony 6.1+) to enforce complexity.

---

### [M2] No rate limiting on authentication endpoint
**OWASP**: A07:2021 – Identification and Authentication Failures
**Affected routes**: `/login`, `/health/ready`

No rate limiting is configured anywhere in the application. An attacker can perform unlimited login attempts (brute force / credential stuffing).

**Recommendation**:
- Install `symfony/rate-limiter` and configure a login limiter:
  ```yaml
  # config/packages/rate_limiter.yaml
  framework:
      rate_limiter:
          login_limiter:
              policy: token_bucket
              limit: 5
              rate: { interval: '1 minute' }
  ```
- Apply it in the firewall config via `login_throttling`.

---

### [M3] Missing HTTP security headers
**OWASP**: A05:2021 – Security Misconfiguration

No security response headers are configured. Missing headers:
- `Content-Security-Policy`
- `X-Frame-Options`
- `X-Content-Type-Options`
- `Referrer-Policy`
- `Permissions-Policy`

**Recommendation**:
- Add a response event listener or use `NelmioSecurityBundle` to inject headers on every response:
  ```
  X-Frame-Options: DENY
  X-Content-Type-Options: nosniff
  Referrer-Policy: strict-origin-when-cross-origin
  Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'
  ```

---

### [M4] Risky raw SQL pattern in `ScoreRepository`
**OWASP**: A03:2021 – Injection
**File**: `src/Repository/ScoreRepository.php:526-542`

```php
$orderField = 'lookup.score';
if (isset($params['max_value']) && $params['max_value'] === 'damage') {
    $orderField = 'lookup.damage';
}
// ...
ORDER BY {$orderField} DESC
```

The `$whereClause` is assembled via string interpolation from `$conditions[]` entries, and `$orderField` is interpolated into a raw SQL string. Currently the values are hardcoded, but the pattern is fragile: a future code change that forwards user input into `$params` would introduce SQL injection.

**Recommendation**:
- Use an explicit allowlist for all interpolated identifiers:
  ```php
  $allowed = ['lookup.score', 'lookup.damage'];
  if (!in_array($orderField, $allowed, true)) {
      throw new \InvalidArgumentException('Invalid order field');
  }
  ```
- Where possible, prefer Doctrine QueryBuilder over raw SQL strings.

---

## Low

### [L1] Admin action audit log missing
**OWASP**: A09:2021 – Security Logging and Monitoring Failures

No audit trail is recorded for admin CRUD operations (score add/edit/delete, user management, news, etc.). In a breach scenario it is impossible to determine what was changed and by whom.

**Recommendation**:
- Implement a simple audit log (entity + action + actor + timestamp) for all admin write operations, stored in the database.
- Use a Doctrine event subscriber or Symfony event listener for consistency.

---

### [L2] Database credentials without password in development `.env`
**File**: `.env:36`

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/wcr_sf?..."
```

The root MySQL user is used without a password. While this is a local dev environment, it sets a risky precedent.

**Recommendation**:
- Use a dedicated database user with a strong password even in development.
- Never commit real credentials; use `.env.local` (already gitignored).

---

### [L3] `APP_ENV` and version exposed in public `/health` response
**File**: `src/Controller/HealthCheckController.php:38`

```json
{ "status": "ok", "environment": "dev", "version": "unknown" }
```

Exposing the environment name (`dev`/`prod`) aids attacker reconnaissance (dev mode may mean debug tools are active).

**Recommendation**:
- Remove `environment` and `version` from the public health check endpoint.

---

## Recommendations Priority Matrix

| Priority | Action |
|----------|--------|
| Immediate | Sanitise `|raw` rich text outputs (C1) |
| Short term | Strip sensitive data from health check endpoints (H1) |
| Short term | Replace `exec()` with Symfony `Process` (H3) |
| Short term | Log exceptions server-side, show generic messages to users (H2) |
| Short term | Add CSRF failure feedback (H4) |
| Medium term | Add rate limiting to `/login` (M2) |
| Medium term | Add HTTP security headers (M3) |
| Medium term | Harden SQL patterns / whitelist identifiers (M4) |
| Medium term | Increase password minimum length to 12 (M1) |
| Ongoing | Implement admin audit logging (L1) |
