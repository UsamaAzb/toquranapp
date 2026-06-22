# TQ9 Browser Push Notifications V1 Plan

Status: production deployed; real-device opt-in/test-push smoke passed
Date: 2026-06-22
Branch: `codex/tq9-launch-readiness`
Scope: outside-app browser/PWA push notifications for parent and student accounts

## Objective

Add simple outside notifications for the To Quran installed browser app / PWA.

The goal is not an in-app notification bell, dropdown, or message center. The goal is that a parent or student can enable notifications on a device and receive useful system/browser notifications even when the app is not currently open.

V1 should support:

- parent and student notification opt-in per device/browser;
- safe browser push notifications for a few high-value events that have clean app hooks;
- privacy-safe notification titles/body text;
- notification click-through back into the correct To Quran app screen;
- stale subscription cleanup;
- graceful behavior when the browser denies or does not support notifications.

## Roadmap Relationship

- TQ9 is active and launch-readiness focused.
- This plan supersedes the in-app notification-center plan for launch work.
- Week14 is still the reuse source, but the useful Week14 reference is planning guidance, not a completed implementation.
- Week14 `docs/SPRINTS.md` describes Web Push as the mobile-like app-notification path and warns that it requires service-worker scope, VAPID keys, subscription storage, permission UX, failed-subscription cleanup, HTTPS verification, and privacy-safe payloads.
- Week14 `docs/SERVER-PUSH-CHECKLIST.md` confirms the shipped PWA slice had manifest/icons/iOS standalone metadata only, with no service worker yet. That matches this repo's current state.

## Current Evidence

To Quran app currently has:

- `routes/web.php` serving `/manifest.webmanifest` through `PwaController::manifest`;
- PWA meta tags in `resources/views/pwa/meta.blade.php`;
- no `/service-worker.js` route/file;
- no Web Push package in `composer.json`;
- no push-subscription model/table/routes;
- `QUEUE_CONNECTION=database` in `.env.example`;
- scheduled commands in `routes/console.php`;
- production cron/queue setup must be re-verified before any queued push delivery is enabled.
- target-device/browser behavior must be verified on the actual phones/browsers the owner plans to use. Web Push support differs by browser and install mode, so unsupported/denied states must be handled as normal.
- iOS/iPhone Web Push requires the site to be installed with Add to Home Screen on iOS 16.4+; Safari-tab-only notifications are not a supported V1 success path.
- No real parent/student has installed or enabled To Quran browser notifications yet, so V1 has no existing push subscriptions to preserve or migrate.

Relevant existing trigger points:

- student tasks entering review through `StudentTaskApprovalService::putToReview()`;
- gift milestones being reached through `RewardProgressionService::advanceGiftQueueForTotal()`, but this service currently returns `void` and does not expose which gift rows newly reached a milestone;
- new student work becoming available is not yet a clean launch hook. The confirmed automated publish path includes the midnight publisher, which is not suitable for waking families with push notifications.

## Implementation Evidence

Implemented locally on 2026-06-22:

- `minishlink/web-push` added through Composer.
- `config/browser-push.php` added with `BROWSER_PUSH_ENABLED=false` by default.
- `PushSubscription` model added for per-user browser/device subscriptions.
- `BrowserPushService` added for subscription storage, delivery, stale endpoint cleanup, task-review pushes, reward-reached pushes, and current-user test pushes.
- `/service-worker.js` added as a push-only root-scope route with no `fetch` handler.
- Authenticated subscribe/revoke/config/test routes added under `browser-push/*`.
- Parent `My Children` page and student workplace now include a small opt-in control only when the current device/browser is not subscribed; once enabled, the card hides itself. No parent/student Test or Turn off buttons remain in launch UI, and no in-app notification center or topbar redesign was added.
- `StudentTaskApprovalService::putToReview()` now sends parent task-review pushes after commit.
- `RewardProgressionService` now exposes every newly reached gift in a points jump and sends parent/student reward pushes after commit.
- Guarded manual SQL patch added at `database/manual/patches/2026-06-22-create-browser-push-subscriptions.sql`.
- `.env.example` documents the VAPID and kill-switch settings.

Verification performed:

- `composer validate --no-check-publish` passed.
- `php artisan route:list --name=browser-push` confirmed the browser-push routes.
- `php artisan route:list --path=service-worker` confirmed the service-worker route.
- `php artisan test tests\Feature\CoreLms\BrowserPushNotificationsTest.php` passed: 4 tests / 16 assertions.
- `php artisan test tests\Unit\StudentTaskApprovalServiceTest.php --filter=reward_progression` passed: 2 tests / 6 assertions.
- `php artisan test tests\Unit\StudentTaskApprovalServiceTest.php` passed: 26 tests / 81 assertions.

Production deployment evidence on 2026-06-22:

- Scoped runtime package uploaded to Hostinger app path:
  `/home/u504065335/domains/toquran.org/public_html/appdashboard`.
- Pre-change DB/file backup captured at:
  `/home/u504065335/tq9-browser-push-tail-backup-20260622-192321`.
- Guarded SQL patch was applied to `u504065335_to_quran`; verification returned `push_subscriptions=1`.
- Composer install completed with `minishlink/web-push` present.
- Production `.env` now has browser push enabled with VAPID keys configured and subject `mailto:support@toquran.org`.
- Laravel `optimize:clear`, `config:cache`, `route:cache`, and `view:cache` completed.
- Public service worker smoke:
  - `https://app.toquran.org/service-worker.js` returned `200`.
  - `Content-Type: application/javascript; charset=UTF-8`.
  - `Service-Worker-Allowed: /`.
  - `Cache-Control: must-revalidate, no-cache, no-store, private`.
  - service worker body contains `push` and `notificationclick`.
  - no `fetch` handler was found.
- `https://app.toquran.org/login` returned `200`, with secure cookies and current security headers.
- Owner real-device test push worked, made the normal phone sound, and opened
  the installed app. A follow-up visual patch added
  `public/pwa/icons/toquran-notification-badge.png` so Android status-bar
  notifications use a transparent monochrome tree badge instead of the
  full-color maskable app icon. The first badge pass showed a tree/book mark;
  the production badge was then polished to a tree-only silhouette after owner
  smoke noticed the book/base line was too visible at status-bar size.
- Android/Chrome may also show its own separate installed-app helper
  notification such as "Tap to copy the URL for this app"; that card is not a
  To Quran server push and is not controlled by the service worker payload.

Remaining work is real-device trigger smoke only: verify task-review and reward-reached pushes.

## Product Decisions

- V1 is browser/PWA push only.
- Do not build an in-app bell, dropdown, list page, or topbar notification redesign in this slice.
- V1 recipients are parents and students only.
- Teacher, support, admin, WhatsApp, email, and n8n notifications are out of scope unless separately approved.
- Notification permission is optional. Denied, unsupported, revoked, or expired subscriptions are normal states, not product failures.
- A user may have multiple device/browser subscriptions.
- The app should show a small, local control such as `Enable notifications on this device` and a simple status. Prefer placing it on existing parent/student surfaces or account settings instead of changing the global navbar.
- Add a production kill switch such as `BROWSER_PUSH_ENABLED=false/true`. When disabled, the app may still save/revoke subscriptions, but it must not send production pushes.

## Privacy Rules

Push payloads must stay generic because browser notifications can appear on a lock screen.

Allowed examples:

- `Maryam has tasks waiting for review.`
- `A reward was reached.`

Avoid:

- private notes;
- full task instructions;
- files or attachment names when sensitive;
- health/family details;
- passwords, PINs, tokens, direct DB IDs, or internal references.

## V1 Trigger Map

Implement only a small useful set first:

1. Parent: child submitted tasks for review.
   - Trigger: a task moves into `in_review`.
   - Deduping: avoid sending one notification per task if several tasks are submitted together. Prefer a short per-child/session/time-window dedupe.
   - Click target: parent review screen or child card route the parent can already access.
   - Existing guard: `StudentTaskApprovalService::putToReview()` already returns early when a task is completed or already in review. V1 dedupe should still exist, but this helps avoid accidental repeats.

2. Parent and student, best effort: reward reached.
   - Trigger: a gift first changes to `reached`.
   - Deduping: one notification per reached gift.
   - Click target: rewards/gift board.
   - Required service change before implementation: `RewardProgressionService::advanceGiftQueueForTotal()` must expose the gift IDs that newly transition to `reached` during that call, either by returning a value or by emitting a reviewed domain event after the transaction.
   - Multi-gift rule: a large point jump can reach several gifts in one call. V1 must notify once per newly reached gift, not once per service invocation.
   - Parent is the reliable recipient. Student push is best effort only when the student has a linked login user/device; skip silently when `students.user_id` is null.

3. Manual/test push.
   - Trigger: authenticated test action for the current user or admin-selected user, depending on final implementation.
   - Purpose: verify the device, service worker, VAPID keys, and notification-click behavior before relying on automatic triggers.

Deferred from V1:

- Student `new work is ready` push. Do not wire this to the midnight publisher. Revisit only after a clean daytime/per-student teacher publish event is identified, or design a morning digest that cannot wake families.

## Technical Approach

Implementation should be app-owned and minimal:

1. Add Web Push dependency after Composer compatibility check.
   - Preferred V1 path: direct `minishlink/web-push` behind a small app service, because this app only needs a small single-app browser-push slice.
   - Only use `laravel-notification-channels/webpush` if the installed major version is confirmed Laravel 12-compatible and its config/VAPID wiring and expected subscription schema are reviewed.
   - Do not continue with a package that forces unsafe framework downgrades.
   - Lock the subscription table shape to the chosen implementation before writing SQL. If using `laravel-notification-channels/webpush`, follow its expected polymorphic subscription schema. If using a direct `minishlink/web-push` service, a simpler `user_id` schema is acceptable.

2. Add a push-only service worker.
   - Serve `/service-worker.js` from app root scope.
   - Prefer serving it through Laravel like `/manifest.webmanifest`, with session/auth middleware stripped so the service worker response does not carry session behavior.
   - Set JavaScript content type.
   - Set `Service-Worker-Allowed: /`.
   - Set conservative service-worker cache headers, for example `Cache-Control: no-cache`, so fixes roll out quickly.
   - Do not add offline caching in V1.
   - Do not cache authenticated HTML, task pages, attachments, or API responses.
   - Handle `push` and `notificationclick` only.
   - Do not add a `fetch` handler in V1. This keeps the service worker from intercepting authenticated pages, storage files, Google/Microsoft document viewer flows, or Livewire requests.
   - `notificationclick` must only open same-origin app paths from a reviewed allow-list. Do not let an arbitrary URL in a payload become an open redirect.

3. Add VAPID configuration.
   - `WEBPUSH_VAPID_PUBLIC_KEY`
   - `WEBPUSH_VAPID_PRIVATE_KEY`
   - `WEBPUSH_VAPID_SUBJECT=mailto:support@toquran.org`
   - `BROWSER_PUSH_ENABLED=false` by default until production smoke passes.
   - Never commit the private key.
   - Public key may be exposed to authenticated frontend code.
   - Keep the VAPID subject/contact aligned with the To Quran support/security contact. For launch, use `mailto:support@toquran.org` unless the owner explicitly approves a different operational inbox.

4. Add manual SQL for push subscriptions.
   - No Laravel migration.
   - Use a guarded manual SQL patch under `database/manual/patches/`.
   - Target guard must confirm `u504065335_to_quran`.
   - Add backup/export evidence before production apply.
   - Add the patch and its execution note to `database/manual/README.md` when the artifact is created/executed so the DB trail stays traceable.

Suggested table shape:

Final columns depend on the selected implementation. The app must not create a `user_id`-only table and then install a package that expects Laravel's polymorphic notification-channel schema.

Direct-service fallback shape:

- `id`
- `user_id`
- `endpoint` unique
- `public_key`
- `auth_token`
- `content_encoding`
- `user_agent` nullable
- `last_seen_at` nullable
- `revoked_at` nullable
- timestamps

5. Add authenticated routes.
   - fetch push config/public VAPID key;
   - subscribe or update this browser endpoint;
   - revoke this browser endpoint;
   - optional authenticated test push endpoint for manual smoke.
   - Validate payload shape and size, including endpoint URL, keys, and content encoding.
   - Throttle subscribe/revoke/test routes.
   - POST/DELETE requests from browser JavaScript must send Laravel's CSRF token and be covered by feature tests.
   - Never accept a client-provided recipient user id; always bind subscriptions to the authenticated user.
   - Frontend subscribe flow must register `/service-worker.js` first, convert the public VAPID key to the `Uint8Array` format required by `PushManager.subscribe()`, and subscribe with `userVisibleOnly: true`.
   - Frontend permission handling must distinguish `default`, `granted`, and `denied`; do not call `Notification.requestPermission()` until the user clicks the enable button.

6. Add delivery service.
   - Scope delivery by recipient user id.
   - Send after the business transaction commits.
   - Do not fail the core task/reward action if push sending fails.
   - Mark 404/410 endpoints revoked.
   - Log failed sends without exposing payload secrets.
   - Build click targets server-side as relative app paths and reject non-app URLs before enqueueing/sending.

7. Queue/scheduler decision.
   - Prefer queued delivery if production queue worker is verified.
   - If queue reliability is not verified, use a reviewed low-volume synchronous fallback after commit or stop before production sending.
   - The plan must not assume Hostinger cron/queue is healthy without fresh evidence.

## Frontend UX

Keep the frontend small:

- button: `Enable notifications on this device`;
- status states: unsupported, denied, enabled, disabled, retry needed;
- short text only where needed;
- no notification center;
- no global navbar redesign;
- no prompt spam. Only ask browser permission after the user clicks the button.
- Launch UX after production smoke: parent/student do not see a persistent test
  or turn-off panel after enabling notifications. The opt-in card is hidden
  when the current browser already has a saved subscription.

Selected V1 surfaces:

- parent `My Children` page;
- student workplace.

## DB And Safety Impact

- Requires one reviewed manual SQL patch for `push_subscriptions`.
- No destructive SQL.
- No migrations/seeders.
- No secrets in repo.
- Production apply requires target confirmation, backup/export evidence, and explicit approval for the exact SQL command/action.

## Public Website Handoff

No public website implementation is planned in V1.

This is for `app.toquran.org` only. The public booking website can continue using email flows and the existing consultation workflow.

## Tests

Automated tests should cover:

- service worker route returns JavaScript with `Service-Worker-Allowed: /`;
- service worker route is public, same-origin, and not behind auth middleware;
- service worker script has no `fetch` event listener;
- unauthenticated users cannot subscribe/revoke;
- authenticated parent/student can subscribe and revoke their own browser endpoint;
- endpoint update is idempotent;
- subscribe/revoke/test endpoints are throttled, require CSRF for browser requests, and ignore client-supplied user ids;
- notification click targets are same-origin app paths, not arbitrary external URLs;
- stale 404/410 endpoints are revoked;
- payloads are privacy-safe and do not include file names, secrets, PINs, or internal DB references;
- task-in-review trigger creates one parent push event per intended dedupe window;
- reward-reached service change exposes every newly reached gift id, including multi-gift point jumps;
- reward-reached trigger creates parent push events once and student push events only when a student user/device exists;
- no student `new work ready` push is emitted from the midnight publisher;
- denied/unsupported frontend state does not break the page.

Manual production smoke should verify:

- app still installs/opens as a browser app;
- user can enable notifications on the owner's real target phone browser/PWA;
- iPhone smoke, if used, is performed from the installed Add-to-Home-Screen app on iOS 16.4+;
- test push arrives outside the app;
- tapping a notification opens/focuses To Quran and reaches the intended safe route;
- task review and reward-reached notifications arrive;
- disabling `BROWSER_PUSH_ENABLED` stops outgoing push delivery without breaking subscribe/revoke UI;
- revoked/denied browser state is handled cleanly;
- Laravel logs do not show push delivery errors.

## Deployment Notes

- Generate VAPID keys locally or on the server without committing them.
- Add keys to production `.env`.
- Apply `database/manual/patches/2026-06-22-create-browser-push-subscriptions.sql` before enabling browser push.
- Treat VAPID key rotation as an operational change: existing browser subscriptions may stop working after rotation, so affected users/devices may need to re-enable notifications.
- Keep `BROWSER_PUSH_ENABLED=false` until production service-worker registration and test push pass.
- Clear/rebuild Laravel config cache after `.env` change.
- Deploy service worker carefully because bad service workers can persist in browsers.
- Keep service worker version comments simple so browsers fetch updates reliably.
- Confirm `/service-worker.js` is served from `https://app.toquran.org/service-worker.js` with JavaScript content type, `Service-Worker-Allowed: /`, and no-cache style headers.
- Verify HTTPS and root scope before enabling production send.
- Local tests can verify routes, service-worker script shape, and frontend permission states, but the real phone notification flow must be smoke-tested on `https://app.toquran.org` with the installed app/browser.

## Non-Goals

- No in-app notification bell/dropdown/list/history.
- No chat/message center.
- No WhatsApp/n8n.
- No email notification rewrite.
- No public website push notifications.
- No offline mode.
- No attachment caching.
- No teacher/support/admin push in V1.

## Stop Conditions

- Stop if no Laravel 12-compatible Web Push package/path is available without risky downgrades.
- Stop if VAPID private key would be committed or exposed to frontend/build output.
- Stop if `BROWSER_PUSH_ENABLED` kill switch is missing.
- Stop if production queue/cron is required but not verified.
- Stop if the service worker breaks login, assets, installability, or document viewing.
- Stop if the service worker includes a `fetch` handler in V1.
- Stop if notification payloads would expose sensitive child/family/task/file details.
- Stop if reward-reached implementation cannot reliably identify every newly reached gift.
- Stop if implementation would send student new-work pushes from the midnight publisher.
- Stop if production DB target or backup/export evidence is missing before applying manual SQL.

## Resolved Build Questions

- Exact placement of the `Enable notifications on this device` control: parent `My Children` page and student workplace.
- Parent/student controls are intentionally local to those existing surfaces for V1; no account/settings page or navbar redesign.
- Manual/test push utility is current-user only for V1, not admin-selectable.
