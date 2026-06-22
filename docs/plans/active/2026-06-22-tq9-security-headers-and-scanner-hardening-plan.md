# TQ9 Security Headers And Scanner Hardening Plan

Status: local implementation deployed to Hostinger; production smoke passed
Date: 2026-06-22
Branch: `codex/tq9-launch-readiness`
Sprint/Roadmap: TQ9 deployment readiness and public website handoff

## Objective

Analyze the public security-scan findings for `https://app.toquran.org/login`, separate real launch risk from scanner checklist gaps, and implement safe hardening without breaking:

- Laravel authentication and CSRF;
- Livewire;
- Vuexy/Tabler assets and theme customizer;
- uploaded Library/task files;
- Google PDF viewer;
- Microsoft Office viewer;
- YouTube embeds;
- Hostinger shared-hosting routing.

This plan does not authorize production edits by itself. Implementation requires explicit owner approval after review.

## Current Evidence

Owner-provided SiteSecurityScore screenshot for `app.toquran.org/login` showed:

- score `19`, grade `F`;
- redirected from `https://app.toquran.org`;
- HTTP headers: `2 present, 13 missing`;
- Content Security Policy: not configured;
- Cookie security: `1/2 secure`;
- DNS security: weak;
- email security: fair;
- `security.txt`: not found;
- page analysis: excellent;
- TLS/HTTPS: good;
- client-code security: good.

Read-only live header check on 2026-06-22 showed:

- app is served over HTTPS by Hostinger/LiteSpeed;
- Laravel session cookie has `Secure`, `HttpOnly`, and `SameSite=Lax`;
- XSRF cookie has `Secure` and `SameSite=Lax` and is intentionally not `HttpOnly` because client-side CSRF helpers may need to read it;
- no visible CSP header;
- no visible hardening headers such as `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, or `Permissions-Policy`;
- `X-Powered-By: PHP/8.3.31` is exposed;
- `Server`/Hostinger platform headers are visible and may be partly controlled by shared hosting.

Current app root `.htaccess` already includes important source-file protection:

- blocks non-`app.toquran.org` hosts from executing the app;
- rewrites `assets`, `images`, `pwa`, `build`, and `storage` to the Laravel `public/` tree;
- blocks direct web access to framework/source directories such as `app`, `config`, `database`, `resources`, `routes`, `storage`, `vendor`, and `node_modules`;
- blocks `.env`, Composer/npm manifests, SQL/log/backup files, `artisan`, `phpunit.xml`, and similar source artifacts.

This means the low scanner score is mainly missing hardening headers and policy files. It is not evidence by itself that login/admin access is easily hacked.

## Implementation Evidence

Local implementation started on 2026-06-22 after the public website hardening pass finished.

Implemented locally:

- root `.htaccess` rewrite for `/.well-known/security.txt` to `public/.well-known/security.txt`;
- root `.htaccess` low-risk header block:
  - `Strict-Transport-Security: max-age=31536000`;
  - `X-Content-Type-Options: nosniff`;
  - `X-Frame-Options: SAMEORIGIN`;
  - `Referrer-Policy: strict-origin-when-cross-origin`;
  - `Permissions-Policy`;
  - `Cross-Origin-Opener-Policy: same-origin-allow-popups`;
  - `Content-Security-Policy-Report-Only`, not enforced CSP;
  - `X-Powered-By` unset attempt;
- project-root `.user.ini` with `expose_php = Off`;
- `public/.well-known/security.txt` with support contact and app canonical URL.

Production deployment evidence on 2026-06-22:

- deployed only root `.htaccess`, root `.user.ini`, and `public/.well-known/security.txt`;
- target path: `/home/u504065335/domains/toquran.org/public_html/appdashboard`;
- remote backup path: `/home/u504065335/domains/toquran.org/public_html/appdashboard/_deploy_backups/20260622-135722-security-headers`;
- no Laravel app code, DB, or `.env` value was changed in this deployment.

Production smoke on 2026-06-22:

- `https://app.toquran.org/login` returned `200 OK`;
- `https://app.toquran.org/.well-known/security.txt` returned `200 OK` and the expected support contact/canonical content;
- `https://app.toquran.org/.env` returned `403 Forbidden`;
- `https://app.toquran.org/composer.json` returned `403 Forbidden`;
- `https://app.toquran.org/.user.ini` returned `403 Forbidden`;
- live response headers include `Strict-Transport-Security`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, and `Content-Security-Policy-Report-Only`;
- `X-Powered-By` was not visible on the checked live responses.

Not implemented in this first local pass:

- no CSP enforcement yet;
- no HSTS `includeSubDomains`;
- no HSTS preload;
- no COEP/CORP global headers;
- no DNS/email record changes;
- no production deployment.

## Week14 Reuse Source Files/Modules

A targeted Week14 search was performed on 2026-06-22 for reusable hardening patterns in:

- `D:\xampp\htdocs\week14-app-lms\.htaccess`
- `D:\xampp\htdocs\week14-app-lms\public\.htaccess`
- `D:\xampp\htdocs\week14-app-lms\app\Http\Middleware\*Security*`
- `D:\xampp\htdocs\week14-app-lms\bootstrap\app.php`
- `D:\xampp\htdocs\week14-app-lms\config\*.php`
- any docs or deployment notes that mention CSP, security headers, HSTS, or `security.txt`.

Result: Week14 has no global CSP/header middleware or `.htaccess` policy to reuse. Week14 does set `X-Content-Type-Options: nosniff` on several protected file responses, which supports keeping per-file `nosniff` behavior, but To Quran still needs its own global header/CSP plan.

## Risk Classification

High-priority launch hardening:

- add missing low-risk security headers;
- remove or suppress `X-Powered-By` where Hostinger allows it;
- add `security.txt`;
- add a CSP in Report-Only mode first;
- verify private/source deny checks still pass after `.htaccess` changes.
- pin `SESSION_SECURE_COOKIE=true` in production so session and XSRF cookies stay secure even before HSTS.

Medium-priority launch hardening:

- tune CSP from Report-Only to enforced after browser smoke shows no breakage;
- decide whether HSTS can safely include subdomains;
- review cookie scanner findings and document which cookies are intentionally JavaScript-readable.
- decide whether to self-host login CDN assets later so the enforced CSP can be tightened.

External/shared-hosting items:

- DNSSEC, SPF/DKIM/DMARC, and server-banner removal may require Hostinger DNS/email/control-panel changes, not app code only.
- These should be tracked but not mixed into Laravel code unless a repo artifact is needed.

## Proposed Solution

### 1. Add Low-Risk HTTP Security Headers

Preferred implementation: root `.htaccess`, because Hostinger serves the app from the project root and current hardening already lives there.

Candidate header set:

```apache
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=(), payment=(), usb=(), bluetooth=()"
    Header always set Cross-Origin-Opener-Policy "same-origin-allow-popups"
    Header always unset X-Powered-By
    Header unset X-Powered-By
</IfModule>
```

Notes:

- `X-Frame-Options: SAMEORIGIN` protects To Quran pages from being embedded by other sites. It does not prevent To Quran from embedding Google/Microsoft viewers.
- `X-Content-Type-Options: nosniff` is low risk for normal Laravel assets if MIME types are correct.
- `Referrer-Policy: strict-origin-when-cross-origin` is a good default for app privacy.
- `Permissions-Policy` disables browser features the LMS does not currently need. Camera and microphone are intentionally disabled because the current app does not host in-app live calls; revisit this before any future embedded video-class feature.
- `Cross-Origin-Opener-Policy: same-origin-allow-popups` is a safe first COOP posture for app pages and preserves deliberate new-tab flows.
- `X-Powered-By` may still appear if Hostinger/LiteSpeed/PHP overrides cannot be changed from `.htaccess`; use `.user.ini` as the reliable Hostinger/PHP-FPM fix and keep the `.htaccess` unset as belt-and-suspenders.

Do not add globally:

- `Cross-Origin-Embedder-Policy`: it can break Google PDF viewer, Microsoft Office viewer, YouTube embeds, and other cross-origin teaching-file flows unless every embedded/fetched resource cooperates.
- `Cross-Origin-Resource-Policy` on global storage/file responses: Google/Microsoft viewer access and public teaching-material fetches may depend on cross-origin access to `/storage/...`.

### 1.1 Suppress PHP Version Header

Preferred Hostinger-compatible implementation:

- create or update project-root `.user.ini`;
- add `expose_php = Off`;
- allow Hostinger/PHP user-ini cache time to refresh before retesting, commonly several minutes.

Keep the `.htaccess` `Header unset X-Powered-By` rule too, but do not rely on it as the only fix.

### 2. Decide HSTS Carefully

Candidate for `app.toquran.org` only:

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

Do not add this until verified:

- `app.toquran.org` is fully HTTPS;
- any future nested subdomains under `app.toquran.org` are HTTPS-ready;
- the owner accepts that browsers will force HTTPS for `app.toquran.org` for the configured period.

Important scope note:

- If HSTS is sent only by `app.toquran.org`, it does not force HTTPS for the parent `toquran.org` or sibling subdomains.
- If the public website later adds HSTS on `toquran.org` with `includeSubDomains`, then all subdomains, including `app.toquran.org`, must be HTTPS-ready.

Safer first pass if unsure:

```apache
Header always set Strict-Transport-Security "max-age=31536000"
```

Do not use preload in launch week.

### 3. Add `security.txt`

Create:

- `public/.well-known/security.txt`

Because Hostinger serves this app from the project root and the current `.htaccess` only rewrites selected public paths into `public/`, the implementation must also make this URL reachable:

- preferred: add a root `.htaccess` rewrite from `^\.well-known/security\.txt$` to `public/.well-known/security.txt`;
- fallback: create the final public file at root `.well-known/security.txt` if the rewrite approach is not reliable on Hostinger.

Rewrite placement:

- place the rewrite inside the root `<IfModule mod_rewrite.c>` block;
- place it after the `app.toquran.org` host restriction;
- place it before the trailing-slash redirect and front-controller rules;
- preferred rule:

```apache
RewriteRule ^\.well-known/security\.txt$ public/.well-known/security.txt [END]
```

- fallback rule if Hostinger/LiteSpeed rejects `[END]`:

```apache
RewriteRule ^\.well-known/security\.txt$ public/.well-known/security.txt [L]
```

Prefer the rewrite approach so `public/.well-known/security.txt` remains the source of truth. If the root fallback is used, verify Hostinger/LiteSpeed serves the `.well-known` dot-directory.

Suggested content:

```text
Contact: mailto:support@toquran.org
Expires: 2027-06-22T00:00:00Z
Preferred-Languages: en, ar
Canonical: https://app.toquran.org/.well-known/security.txt
Policy: https://toquran.org/security-policy
```

The `Policy:` URL should point to a real security policy page. Coordinate with the website repo to create `https://toquran.org/security-policy` before publishing that line. If the page is not ready, omit `Policy:` from the first `security.txt` rather than pointing it at the homepage.

If the public website should own the canonical `toquran.org/.well-known/security.txt`, coordinate with the website repo and keep the app copy consistent.

### 4. CSP Phase 1: Report-Only

Because the app uses Livewire, Vite-built assets, Vuexy inline configuration, Google viewer, Microsoft viewer, and YouTube embeds, CSP should be introduced in Report-Only mode first.

Before writing the first CSP policy:

- open representative admin, teacher, parent, student, Library, task viewer, Google PDF, Microsoft Office, and YouTube pages;
- inspect browser console/network sources for external script, style, font, image, media, frame, and connect origins;
- add only the exact origins that are genuinely required.

Preferred implementation options:

- `.htaccess` header if static policy is enough;
- Laravel middleware if the policy needs route-specific exceptions or future nonce work.

Initial Report-Only candidate:

```text
Content-Security-Policy-Report-Only:
  default-src 'self';
  base-uri 'self';
  object-src 'none';
  frame-ancestors 'self';
  form-action 'self';
  img-src 'self' data: blob: https:;
  media-src 'self' blob: https:;
  font-src 'self' data: https://fonts.gstatic.com;
  script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.jsdelivr.net;
  style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com;
  connect-src 'self';
  worker-src 'self' blob:;
  frame-src 'self' https://docs.google.com https://view.officeapps.live.com https://www.youtube.com https://www.youtube-nocookie.com;
```

Why `unsafe-inline` and `unsafe-eval` initially:

- Vuexy/template customizer and some page scripts may still rely on inline script/config.
- Removing these safely requires a separate nonce/refactor pass.

Required CSP additions if smoke reveals blocked sources:

- add exact Google/Microsoft/YouTube domains observed in browser console;
- add any required image/media domains only if real app usage needs them;
- do not add broad wildcards unless there is a clear reason.

Known required external origins already present in current code:

- `/login` uses `https://cdn.jsdelivr.net` for Bootstrap CSS/JS;
- `/login` uses `https://code.jquery.com` for jQuery;
- authenticated layouts use `https://fonts.googleapis.com` and `https://fonts.gstatic.com` for Google Fonts.

`.htaccess` implementation note:

- The readable CSP block above must be flattened into one physical `Header always set Content-Security-Policy-Report-Only "..."` line inside `<IfModule mod_headers.c>` for Apache/LiteSpeed.
- Do not paste the multi-line example directly into `.htaccess`.

Later tightening path:

- self-host or Vite-bundle login Bootstrap/jQuery assets;
- add Subresource Integrity if CDN assets remain;
- remove CDN origins from CSP only after the login page no longer depends on them.

### 5. CSP Phase 2: Enforce

Only after Report-Only smoke is clean:

- switch from `Content-Security-Policy-Report-Only` to `Content-Security-Policy`;
- keep the same policy first;
- run full smoke again;
- later harden further by replacing inline scripts with nonces or moving config into external assets.

### 6. Cookie Scanner Review

Do not blindly force `HttpOnly` on every cookie.

Expected cookie decisions:

- Laravel session: must be `Secure`, `HttpOnly`, `SameSite=Lax`.
- XSRF token: should be `Secure`, `SameSite=Lax`, but not necessarily `HttpOnly`.
- Production `.env`: set `SESSION_SECURE_COOKIE=true` so Laravel session and XSRF cookies are always emitted with `Secure` behind Hostinger.
- Theme/layout/customizer cookies are client-side UI preference cookies written by vendor JavaScript. They are not sensitive authentication cookies, may not appear in `Set-Cookie` response headers, and should be documented as low-risk unless we intentionally patch vendor `_setCookie` behavior.
- Scanner expectation: the cookie score may still report `1/2` if it penalizes the intentionally JavaScript-readable XSRF cookie for not being `HttpOnly`. Do not "fix" that by making XSRF `HttpOnly` unless the CSRF/frontend pattern is redesigned and retested.

Optional theme-cookie hardening:

- patch the vendor/customizer cookie writer to append `Secure` on HTTPS and `SameSite=Lax`;
- re-run full theme customizer smoke, especially primary-color persistence and the blue/gold swatch behavior fixed in `2df85eb`;
- do not mark this as a launch blocker unless the scanner specifically treats a non-sensitive UI cookie as unacceptable.

Verification:

- inspect `Set-Cookie` headers for login and authenticated pages;
- inspect browser Application/Cookies or `document.cookie` for JavaScript-written theme/layout cookies;
- confirm no sensitive cookie is missing `Secure`;
- confirm session cookie is `HttpOnly`;
- document intentional non-`HttpOnly` cookies.

### 7. DNS And Email Security Follow-Up

These likely live outside the Laravel repo:

- SPF;
- DKIM;
- DMARC;
- DNSSEC;
- CAA records;
- MX alignment for `support@toquran.org`.

Plan action:

- gather current DNS/email records from Hostinger or DNS lookup;
- create a short owner-facing checklist of required Hostinger changes;
- do not edit app code for DNS/email-only findings.

## To Quran-Specific Changes

Expected app repo changes after approval:

- update `.htaccess` with safe headers;
- add `public/.well-known/security.txt`;
- add the `.well-known/security.txt` rewrite or root fallback needed by the Hostinger project-root layout;
- add or update project-root `.user.ini` with `expose_php = Off`;
- set `SESSION_SECURE_COOKIE=true` in production `.env`;
- optionally add Laravel middleware/config for CSP Report-Only if `.htaccess` is too rigid;
- add tests or smoke scripts that assert key headers on local/prod-equivalent responses;
- update deployment evidence notes after production deployment.

Special To Quran constraints:

- CSP must allow Google PDF viewer for task/Library PDFs;
- CSP must allow Microsoft Office viewer for Word/PowerPoint/Excel;
- CSP must not block YouTube repetition videos;
- CSP must allow current `/login` CDN assets until they are self-hosted or bundled;
- CSP must allow current Google Fonts origins until fonts are self-hosted or removed;
- storage URLs under `/storage/...` must remain accessible for normal teaching materials because the approved document viewer strategy depends on public-by-path task snapshots;
- source/private deny rules must remain stronger than scanner-header work.

## DB Impact

No database schema or data change is expected.

No manual SQL is expected.

Backup/export is not required for header-only changes, but production file backup is required before replacing `.htaccess`, `.user.ini`, `.env`, or public files on Hostinger. Keep the previous `.htaccess` available for one-step rollback.

## Public Website Handoff

This plan targets `app.toquran.org`.

Potential website repo follow-up:

- mirror low-risk headers on `toquran.org`;
- decide whether `security.txt` canonical belongs on the website root;
- create a real public security policy page before using `Policy: https://toquran.org/security-policy`;
- ensure HSTS decisions are coordinated across the main domain and app subdomain;
- verify the public website does not iframe app pages before enforcing `X-Frame-Options: SAMEORIGIN` / `frame-ancestors 'self'`;
- ensure public booking/contact forms still submit after any CSP work.

## Test And Verification Scope

Local verification after implementation:

- `php artisan test` for any new header/config tests;
- focused auth/login smoke;
- Vite build only if changed frontend assets require it;
- local HTTP header check using `Invoke-WebRequest` or equivalent only when served through Apache/XAMPP or another server that honors `.htaccess`.
- browser console/network check for expected CSP Report-Only violations on representative pages.

Verification caveat:

- `php artisan serve` will not prove `.htaccess` header behavior.
- PHPUnit can verify Laravel middleware/config behavior if headers are implemented in Laravel, but root `.htaccess` headers must be verified through Apache/XAMPP, Hostinger, or a production-equivalent web server.

Production verification after owner-approved deployment:

- `https://app.toquran.org/login` returns 200;
- `https://app.toquran.org/.well-known/security.txt` returns 200 with the expected `Contact`, `Expires`, and `Canonical` fields;
- `/.env`, `/composer.json`, `/vendor/autoload.php`, `/database/`, `/routes/web.php`, and `/storage/logs/` remain denied;
- non-app host access still fails with 403 or equivalent after `.htaccess` edits;
- headers include:
  - `X-Content-Type-Options: nosniff`;
  - `X-Frame-Options: SAMEORIGIN`;
  - `Referrer-Policy: strict-origin-when-cross-origin`;
  - `Permissions-Policy`;
  - `Cross-Origin-Opener-Policy: same-origin-allow-popups`;
  - optional HSTS if approved;
  - CSP Report-Only or CSP depending on phase.
- `X-Powered-By` is absent after `.user.ini` propagation, or documented as Hostinger-controlled if still present;
- login still works;
- login Bootstrap CSS/JS and password toggle still work;
- admin dashboard still loads;
- student/parent/teacher pages still load;
- Livewire actions still work;
- theme customizer still works;
- Google PDF viewer still renders a real Tajweed PDF;
- Microsoft Office viewer smoke runs if an Office file exists;
- YouTube repetition video attachment still embeds;
- public website booking/contact flow is not affected if headers are mirrored there.

External scanner verification:

- rerun SiteSecurityScore after the first production header pass;
- compare categories before/after;
- do not chase a perfect score if it requires breaking the app or adding unsafe broad CSP rules.

## Stop Conditions

Stop and revert if:

- login breaks;
- login CDN assets are blocked before they are self-hosted;
- Livewire requests fail;
- task attachment viewer cannot render PDFs;
- YouTube embeds fail;
- Google/Microsoft viewer is blocked by CSP;
- Google Fonts or key app typography is blocked unexpectedly;
- source/private deny checks regress;
- Hostinger rejects `.htaccess` syntax and returns 500;
- HSTS would affect an HTTP-only subdomain or unresolved deployment path.

## Non-Goals

- Do not rewrite the authentication system.
- Do not move all uploads to private storage.
- Do not remove Google/Microsoft document viewer support.
- Do not enforce a strict nonce-only CSP in the first pass.
- Do not promise a perfect external scanner score.
- Do not change DNS/email records from code.
- Do not deploy production changes until explicitly approved.
