# Security Hardening Guide

This document lists concrete steps to harden authentication and endpoints. Each item includes a short, copy-paste snippet and the suggested file to edit.

Note: Apply all changes in production and verify behavior in staging first.

---

## Quick checklist
- Use HTTPS for all external API endpoints
- Send credentials in POST body (not URL query)
- Regenerate session after successful login (session fixation)
- Throttle login attempts
- Minimize and sanitize logging
- Secure session cookies and consider encrypting session data
- Generate strong passwords for local user records
- Retry external API calls with backoff (bounded)
- Limit session payload to non-sensitive data only
- Optionally add verified email requirement on privileged routes
- Force HTTPS scheme in production

---

## 1) External API over HTTPS (.env)
File: .env

```dotenv
# Use HTTPS endpoints in production
EXTERNAL_API_LOGIN_URL=https://your-auth-host.example/api/Login/LoginAction
EXTERNAL_API_TEACHER_LOGIN_URL=https://your-auth-host.example/api/Teacher/Login
```

Also set in config/app.php to rely on env (already present); ensure env values are updated for production.

---

## 2) Send credentials in POST body (not query)
File: app/Http/Controllers/Auth/AuthenticatedSessionController.php

Replace any code building query strings like:

```php
$response = Http::timeout(30)->post($apiUrl . '?' . http_build_query([
    'user' => $validated['user'],
    'pass' => $validated['pass'],
    'logintype' => $loginType,
]));
```

With a POST body and retries:

```php
use Illuminate\Support\Facades\Http;

$response = Http::timeout(30)
    ->asForm() // send as application/x-www-form-urlencoded
    ->retry(3, 200) // 3 attempts, 200ms delay
    ->post($apiUrl, [
        'user' => $validated['user'],
        'pass' => $validated['pass'],
        'logintype' => $loginType,
    ]);
```

And for teacher login:

```php
$response = Http::timeout(60)
    ->asForm()
    ->retry(3, 200)
    ->post($teacherApiUrl, [
        'loginType' => 'teacher',
        'deptid' => 1,
        'user' => $validated['user'],
        'pass' => $validated['pass'],
    ]);
```

---

## 3) Regenerate session after login (prevents session fixation)
File: app/Http/Controllers/Auth/AuthenticatedSessionController.php

Immediately after Auth::login(...):

```php
use Illuminate\Support\Facades\Auth;

Auth::login($user);
request()->session()->regenerate();
```

Apply this in both successful student and teacher login paths.

---

## 4) Throttle login attempts
File: routes/auth.php

Add throttling to POST /login (for example, 5 requests per minute per IP):

```php
Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1');
```

You may tune the limits to your needs.

---

## 5) Minimal and sanitized logging
File: app/Http/Controllers/Auth/AuthenticatedSessionController.php

Avoid logging full API responses or sensitive fields. Example safe logging:

```php
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

Log::info('Student login API call', [
    'endpoint' => $apiUrl,
    'timeout' => 30,
]);

// When you must log API data, strip sensitive keys:
Log::debug('Student API response (redacted)', Arr::except($data, ['Password', 'pass', 'token']));
```

In production, reduce log verbosity:

```dotenv
# .env (production)
LOG_LEVEL=warning
```

---

## 6) Secure session cookies and consider encrypting session data
Files: .env and config/session.php

.env (production recommendations):

```dotenv
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SESSION_ENCRYPT=true
```

config/session.php (ensure flags are driven by env):

```php
'http_only' => true,
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => env('SESSION_SAME_SITE', 'lax'),
```

Note: If enabling SESSION_ENCRYPT, ensure you do not store large payloads in session.

---

## 7) Strong password generation for local user records
File: app/Models/User.php

Use high-entropy passwords (never store API credentials). Either use Str::password (Laravel 10+) or random_bytes fallback:

```php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

$securePassword = method_exists(Str::class, 'password')
    ? Str::password(20) // 20 chars, mixed sets
    : bin2hex(random_bytes(16)); // 32 hex chars

$hashed = Hash::make($securePassword);
```

Integrate this into getOrCreateSecurePassword(...). Keep existing behavior of not regenerating for existing users.

---

## 8) Retry external API calls with bounded backoff
File: app/Http/Controllers/Auth/AuthenticatedSessionController.php

Add small retries for transient network issues only (already shown in #2). Example with conditional retry:

```php
use Illuminate\Http\Client\ConnectionException;

$response = Http::timeout(30)
    ->asForm()
    ->retry(3, 200, function ($exception, $request) {
        return $exception instanceof ConnectionException; // only retry on connection errors
    })
    ->post($apiUrl, $payload);
```

Keep total retry count and delay conservative to avoid amplifying upstream load.

---

## 9) Limit session payload to non-sensitive data
File: app/Http/Controllers/Auth/AuthenticatedSessionController.php

Replace broad storage of the entire API response with minimal, non-sensitive fields:

```php
// Old:
// Session::put('user', $data);

// New:
Session::put('user', [
    'api_id' => $data['Id'] ?? null,
    'login_type' => $loginType,
]);
```

Remove any raw passwords, tokens, or full API bodies from session.

---

## 10) Add email verification to privileged routes (optional, policy-based)
File: routes/web.php

If your policy requires verified emails for admin/teacher/advisor areas:

```php
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

Route::middleware(['auth', 'verified', 'teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
});

Route::middleware(['auth', 'verified', 'advisor'])->group(function () {
    Route::get('/advisor/dashboard', [AdvisorDashboardController::class, 'index'])->name('advisor.dashboard');
});
```

---

## 11) Force HTTPS scheme in production
File: app/Providers/AppServiceProvider.php (boot method)

```php
use Illuminate\Support\Facades\URL;

if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

---

## 12) If/when adding JSON APIs, require auth and throttling
File: routes/api.php (example)

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->get('/me', function (Request $request) {
    return $request->user();
});
```

Requires Sanctum or another token mechanism; do not expose state-changing endpoints without auth + throttling.

---

## References
- Laravel HTTP Client: retry(), timeout(), asForm()
- Laravel Authentication & Sessions
- OWASP ASVS / Cheat Sheets (Authentication, Session Management, Logging)
