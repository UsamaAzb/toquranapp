# TQ9 App Notifications V1 Plan

Status: superseded/deferred; do not implement for TQ9 launch unless re-approved
Date: 2026-06-22
Branch: `codex/tq9-launch-readiness`
Scope: launch-friendly in-app notifications for parent/support/admin/teacher workflows

Owner update on 2026-06-22: the desired launch feature is outside-app browser/PWA push notifications for parent/student devices, not an in-app navbar bell or notification center. This plan is kept as historical analysis only. Use `docs/plans/active/2026-06-22-tq9-browser-push-notifications-v1-plan.md` for the active notification work.

## Objective

Add a simple, reliable app notification system inside the private To Quran LMS.

V1 should make important app events visible without requiring the owner or staff to remember hidden commands or manually inspect every page:

- a navbar bell with unread count;
- a compact dropdown with recent notifications;
- a notification list/history page;
- mark one / mark all as read;
- safe links to the relevant app screen;
- database-backed delivery that works on Hostinger without Web Push.

This is not the full chat/message-center sprint and not n8n/WhatsApp automation.

## Roadmap Relationship

- TQ9 is active and launch-readiness focused.
- Week14 roadmap says `App Notifications V1 Full` belongs after support/questionnaire trigger rules are known, but Week14 did not ship a complete notification implementation to copy.
- To Quran launch already has enough real triggers to justify a smaller first production slice:
  - student tasks entering review;
  - gift milestones being reached;
  - gift milestones staying unredeemed;
  - new booking/intake review visibility;
  - transfer/support assignment visibility.
- This plan keeps Web Push out of V1 because it adds service-worker, VAPID, permission, endpoint cleanup, privacy payload, and mobile-browser differences. We can add it later after the in-app notification model is stable.

## Current Evidence

To Quran app currently has:

- `App\Models\User` uses Laravel `Notifiable`.
- No real app notification table/model/screen exists.
- Vuexy notification dropdown styles/scripts exist in vendor assets, but the navbar does not render a real notification dropdown.
- `resources/views/layouts/sections/navbar/navbar-partial.blade.php` is the shared navbar surface for parent/student/teacher/admin/support pages.
- `AppServiceProvider` already composes navbar data through `layouts.sections.navbar.navbar-partial`.
- Task review state exists through `session_task_student.status = in_review` and `StudentTaskApprovalService::putToReview()`.
- Gift milestone state exists through `student_gifts.status`, `reached_at`, and `redeemed_at`, with mutations owned by `RewardProgressionService`.
- Family support assignment exists through `parents.family_support_id`.
- Booking queue and transferred-family surfaces already exist for admin/support.

Week14 reference:

- `D:\xampp\htdocs\week14-app-lms\docs\SPRINTS.md` has `App Notifications V1 Full` planning notes.
- `D:\xampp\htdocs\week14-app-lms\docs\CUSTOMER-SUPPORT-OPERATING-MODEL.md` says notifications should follow native support workflow boundaries.
- `D:\xampp\htdocs\week14-app-lms\docs\SERVER-PUSH-CHECKLIST.md` documents queue/runtime caveats, but there is no complete reusable app notification implementation.

## Product Decisions

V1 delivery:

- Use in-app database notifications first.
- Do not add Web Push in V1.
- Do not add email delivery in this slice except where existing email flows already exist.
- Do not add WhatsApp/n8n delivery in this slice.

Privacy:

- Notification payloads should be useful but not overly sensitive.
- Example: `Maryam has tasks waiting for review` is okay.
- Avoid putting private notes, full files, credentials, or detailed child health/family content in payloads.

Routing:

- Every notification should point to a safe app route the recipient can already access.
- If the recipient loses access later, the route must fail normally through existing auth/permission checks.
- Route targets must be resolved by recipient role, not reused blindly across roles.
- Opening a notification should mark it read, then redirect to the route if still allowed by the notification open handler.
- For support family-workspace notifications, do not rely on `admin.families.show` to enforce current assignment. That route currently allows `customer_support` broadly, so `NotificationController@open` must perform the current `parents.family_support_id` check before deep-linking support users to a family workspace.

Support:

- Support notifications route from `parents.family_support_id`.
- Notifications must not give support users new permissions by themselves.
- Support can see a notification only if they are the assigned support owner or have an existing admin/superadmin role.
- If a support user is later unassigned from a family, old notification rows may remain in that user's history for audit/context, but opening the notification must re-check current assignment before redirecting. If the user no longer has access, mark/read behavior may still work, but target navigation must fall back to the notification index with a safe message.

## Data Model

Use Laravel's standard database notification table shape, created by guarded manual SQL instead of Laravel migrations:

- `notifications`
  - `id` char(36) / uuid primary key
  - `type`
  - `notifiable_type`
  - `notifiable_id`
  - `dedupe_key` nullable string, 191 chars, for repeatable app triggers
  - `data` json
  - `read_at`
  - timestamps

Add indexes in the manual SQL patch:

- index `notifiable_type`, `notifiable_id`, `read_at`, `created_at` for navbar unread/latest queries;
- unique nullable index on `dedupe_key`;
- dedupe keys must be globally stable and recipient-scoped, for example `task-review:{session_task_student_id}:user:{user_id}`. This lets the same event notify both parent and support without duplicates for either recipient.

Payload shape inside `data`:

- `title`
- `body`
- `category`
- `severity`
- `route_name`
- `route_params`
- `actor_user_id`
- `student_id`
- `parent_id`
- `booking_id`
- `booking_child_id`
- `gift_id`

Add a small application service to create notifications consistently:

- `App\Services\AppNotificationService`
  - resolves recipients;
  - applies dedupe keys for repeatable triggers;
  - writes database notifications through direct inserts that can populate `dedupe_key`;
  - keeps payloads small and safe.

Do not rely on Laravel's default database notification channel for deduped V1 notifications, because the default channel will not populate the custom `dedupe_key` column. For V1, prefer direct inserts through `AppNotificationService`; keep any `App\Notifications\AppDatabaseNotification` class as a payload helper only if it is useful.

Notification writes are best-effort side effects. Domain actions such as task review, gift progression, and support assignment must not fail or roll back because notification creation fails. Emit notifications after the business transaction commits, for example with `DB::afterCommit()` plus guarded `try/catch`, or an equivalent safe pattern. Do not implement V1 notification jobs/listeners with `ShouldQueue` unless a queue worker is intentionally configured and monitored.

The service must validate `route_name` and scalar `route_params` before writing. Use an explicit role-based route allowlist/map, for example parent routes for parent recipients, support-safe admin routes for assigned support users, and admin-only routes for admin/superadmin recipients. If a route is missing, not allowed for the recipient role, or needs unsafe params, fall back to the notification index instead of storing a broken link.

Do not create a separate custom notification table unless the standard Laravel table plus `dedupe_key` column proves insufficient.

## Initial Trigger Map

### 1. Task Submitted For Review

Trigger:

- `StudentTaskApprovalService::putToReview()`.

Recipients:

- parent user for the student;
- assigned family support user if present;
- optionally teacher for the task subject/class if the current teacher workflow needs it after review.

Notification:

- title: `Task ready for review`
- body: `{Student first name} submitted task work for review.`
- route:
  - parent: `parent.task-approvals` with the child/student id;
  - support/admin: `admin.families.show` when parent id is known, otherwise `admin.bookings.transferred` with a search hint;
  - teacher: `teacher.task-approvals` only if teacher notifications are enabled and the teacher has the exact student/subject context.

Dedupe:

- one notification per `session_task_student_id` and recipient for the launch implementation;
- if future flows allow the same task to leave and re-enter `in_review`, add a reviewed transition/version suffix to the dedupe key before allowing repeat notifications.

### 2. Gift Reached

Trigger:

- `RewardProgressionService::advanceGiftQueueForTotal()` when a gift first changes to `reached`.

Recipients:

- parent user for the student;
- assigned family support user if present.

Notification:

- title: `Gift reached`
- body: `{Student first name} reached a reward gift.`
- route:
  - parent: `student.journey.board` for that child/student;
  - support: `admin.families.show` for the family, gated by the notification open handler's current support-assignment check;
  - admin/superadmin: `admin.families.show` for the family, or `admin.students.show_reward` when useful;
  - student self-notification is not part of V1 unless explicitly added during implementation.

Dedupe:

- one notification per `student_gift_id` when `reached_at` is first set.
- `RewardProgressionService::advanceGiftQueueForTotal()` may newly reach multiple gifts in one call after a large points jump. The trigger must collect every gift whose `reached_at` was set in that call and create one deduped notification per reached gift, not one notification per service invocation.

### 3. Unredeemed Gift Follow-Up

Trigger:

- scheduled command, daily:
  - reached gift still not redeemed after 7 app-local calendar days;
  - reached gift still not redeemed after 15 app-local calendar days.

Recipients:

- parent user for 7-day reminder;
- parent user and assigned support user for 15-day reminder.

Notification:

- title: `Gift still waiting`
- body: `{Student first name} has a reached gift waiting to be claimed.`

Dedupe:

- one notification per gift and threshold (`7d`, `15d`).

Runtime:

- this requires Laravel scheduler already configured on Hostinger.
- no queue worker is required if notifications are written synchronously.

### 4. New Booking / Intake Review

Trigger:

- V1 default: defer live booking notifications unless a clean app-side trigger point is confirmed.
- If booking notifications are included, implement them as an app-side scheduled watcher/digest command over rows already written into the shared DB by the public website:
  - new pending `bookings` rows;
  - new pending `booking_intake_review` rows;
  - bounded lookback window, for example the last 48 hours on each run;
  - dedupe by `booking:{id}` or `intake-review:{id}`.
- Do not edit public website write paths hastily just to emit notifications.

Recipients:

- active admin/superadmin operators only.
- Exclude inactive staff users.

Notification:

- title: `New booking received` or `Booking needs review`
- body: `A new family intake is waiting in the booking queue.`

Route:

- `admin.bookings.livewire` for normal booking queue rows;
- `admin.bookings.intake-review` for intake-review/problem rows.

Dedupe:

- one per booking or intake review id.

Runtime:

- Uses Laravel scheduler if enabled.
- Does not require a queue worker.
- If the scheduler is not reliable yet, leave booking/intake notifications out of V1 and rely on the existing admin booking queue.

### 5. Family Support Assignment

Trigger:

- `TransferredChildren::assignFamilySupport()`.
- Do not notify when the assignment is cleared to `null`.
- Do not notify on a no-op save where the support user did not change.

Recipients:

- assigned customer support user;
- admin/superadmin can rely on existing page feedback, not necessarily notification.

Notification:

- title: `Family assigned to you`
- body: `{Parent name} is now assigned to you.`

Route:

- `admin.families.show` with the parent id, because support users already access the admin family workspace route through `role:admin|super_admin|customer_support`.

Dedupe:

- one per parent/support assignment pair, unless reassigned after clearing or to a new support user.

## UI Plan

Navbar:

- Add a bell icon near the theme/user controls.
- Show unread count as a small badge.
- Dropdown should show:
  - latest unread/recent notifications;
  - title, short body, age;
  - mark-read action;
  - `View all` link;
  - empty state.
- Keep it mobile-safe; the dropdown should not overflow the viewport.

Notification index:

- route: `/notifications`
- roles: any authenticated user.
- list own notifications only.
- For support users, old notifications may remain visible after reassignment, but target opening must enforce current family access through the route allowlist/current-assignment check.
- filters:
  - all;
  - unread;
  - read.
- actions:
  - open notification target and mark read;
  - mark one read;
  - mark all read.

Vuexy reuse:

- Reuse the existing Vuexy notification dropdown classes and the existing `resources/assets/js/main.js` behavior where it helps.
- Do not rely only on client-side class toggles; read/unread state must persist server-side.

## Backend Implementation Plan

1. Add guarded manual SQL patch for the `notifications` table.
   - Confirm target DB.
   - Use `CREATE TABLE IF NOT EXISTS`.
   - Include the `dedupe_key` column and the navbar/dedupe indexes described above.
   - Include rollback notes but do not drop the table in rollback unless explicitly approved.

2. Add notification service and payload object/class.
   - `App\Services\AppNotificationService`
   - optional `App\Notifications\AppDatabaseNotification` as a DTO/payload helper only, not the default Laravel database channel for deduped writes.
   - direct insert into the Laravel-compatible `notifications` table for V1.
   - after-commit/best-effort writes so notification failures cannot roll back core domain actions.
   - payload validation/sanitization.
   - explicit role-based route allowlist/map.

3. Add controller/routes.
   - `NotificationController@index`
   - `NotificationController@markRead`
   - `NotificationController@markAllRead`
   - `NotificationController@open`

4. Add navbar composer data.
   - unread count;
   - latest notifications;
   - keep query bounded, for example latest 5-8 items and unread count only.

5. Add Blade partial for dropdown and notification list.
   - use stable classes and accessible labels.
   - no large custom design system.

6. Wire initial triggers one at a time.
   - task submitted for review;
   - gift reached;
   - unredeemed gift scheduled reminders;
   - family support assignment;
   - booking/intake watcher only if the scheduled app-side polling path is chosen; otherwise defer.

7. Add scheduled command for unredeemed gift reminders.
   - idempotent, bounded, and safe to run daily.
   - dedupe per `student_gift_id` and threshold (`7d`, `15d`) using app-local dates.
   - document Hostinger scheduler dependency.

8. Add retention/pruning posture.
   - V1 keeps notification rows; no automatic deletion in the launch slice.
   - Add a follow-up note to consider pruning read notifications older than 180 days after real usage is known.

9. Update deployment/runtime docs.
   - scheduler requirement;
   - no Web Push/VAPID keys in V1;
   - no queue worker requirement because V1 notification emission must stay synchronous/direct-insert after commit;
   - if a future implementation chooses queued notifications, document and monitor the required worker before enabling that path.

## DB / Safety Plan

DB impact:

- adds `notifications` table if missing;
- inserts notification rows only as app events happen.
- no deletion/pruning in V1.

Required artifacts:

- `database/manual/patches/YYYY-MM-DD-add-app-notifications-table.sql`
- execution note after local/prod application.

Production run rules:

- backup/export evidence first;
- target DB `u504065335_to_quran` confirmed;
- exact SQL command approved before execution;
- no destructive cleanup in this sprint.

## Public Website Handoff

No public website code should be required for V1.

Public website impact is only indirect:

- booking submissions may create app-side admin notifications only through the app scheduled watcher/digest command if that optional V1 trigger is implemented;
- no website UI should promise push notifications, message center, WhatsApp reminders, or real-time alerts yet.

If the scheduled booking watcher is not implemented in V1, booking notifications are explicitly deferred. Do not edit website write paths hastily.

## Verification Plan

Automated tests:

- notifications table patch can be represented in test schema helpers;
- service creates notification with safe payload;
- service dedupes repeat trigger;
- dedupe key is stored outside JSON and indexed/queryable;
- route target resolution falls back safely when route params are missing or the role-based route allowlist does not permit the target;
- navbar composer returns bounded unread/latest notifications;
- mark read / mark all routes affect only the authenticated user's notifications;
- task review trigger notifies parent/support;
- notification write failure does not roll back task-review submission, gift progression, or support assignment;
- gift reached trigger notifies parent/support once per newly reached gift;
- a points jump that reaches two gifts creates exactly two gift-reached notifications, one per gift;
- 7/15-day gift reminder command is idempotent;
- support assignment trigger notifies the newly assigned support user, skips null clears, and skips no-op re-saves of the same support user;
- optional booking watcher, if implemented, notifies active admin/superadmin users once per booking/review row and ignores old rows outside the lookback window;
- unauthorized users cannot open another user's notification route through notification id.

Manual smoke:

- parent logs in and sees unread notification count;
- parent opens notification dropdown and navigates to the correct child/task/gift page;
- support sees current assigned-family notifications; old reassigned-family notifications may remain in history but cannot open the family workspace;
- support reassignment smoke confirms `NotificationController@open` redirects stale support-family notifications to the notification index after access is removed. This does not claim the destination family route itself blocks direct URL access for support users.
- admin/superadmin notification list loads;
- marking read updates count after navigation and page refresh;
- mobile navbar/dropdown remains usable.

Production smoke:

- create one test review task for demo family;
- put it in review;
- confirm parent notification appears;
- mark it read;
- redeem/reach one test gift only if safe for demo state, otherwise use local smoke only;
- do not leave extra smoke family rows behind.

## Non-Goals

- No Web Push.
- No VAPID keys.
- No service-worker notification logic.
- No native mobile app push.
- No WhatsApp/n8n delivery.
- No chat/message center.
- No support Kanban/work-item implementation.
- No email template rebuild.
- No broad permission expansion for support users.
- No notification content containing passwords, PINs, raw private notes, or attachment URLs.
- No notification pruning/deletion automation in V1.

## Open Questions Before Implementation

- Should teacher receive task-review notifications in V1, or should this start parent/support only?
- If booking watcher is included, should admin/superadmin receive every new pending booking, or only intake-review/problem bookings?
- Should parent gift reminders appear only in-app, or also as email later?
- Should notifications auto-mark as read when opening the linked route, or require explicit mark-read? Recommendation: opening marks read.

## Recommended V1 Cut

Implement in this order:

1. table + service + navbar/list UI;
2. task-review notifications;
3. gift-reached notifications;
4. support-assignment notifications;
5. unredeemed-gift reminder command;
6. booking/intake scheduled watcher only if chosen after implementation inspection; otherwise defer it.

This gives immediate value without turning notifications into a risky pre-launch push/email project.
