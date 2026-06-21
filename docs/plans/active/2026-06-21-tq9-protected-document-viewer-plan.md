# TQ9 External Document Viewer Plan

Status: production code slice deployed; PDF HTTP/gview smoke passed; owner visual phone smoke and Office-file smoke pending
Date: 2026-06-21
Branch: `codex/tq9-launch-readiness`
Sprint/Roadmap: TQ9 launch readiness; supports TQ4 task attachments, TQ6 Library resources, and demo-family launch smoke

## Objective

Make task and Library document viewing usable on phones and tablets without sending the student out of the app.

Final owner decision:

- Use Google viewer for PDFs everywhere in the first pass.
- Use Microsoft Office web viewer for Word, PowerPoint, Excel, and similar Office files.
- Keep the To Quran full-screen task attachment viewer as the app shell, with one app-level attachment navigation bar and one close button.
- Do not return to the custom PDF.js/canvas renderer unless a separate future spike proves it is reliable and polished.

## Current Evidence

- Native browser PDF iframe is stable on desktop but gives a poor phone/PWA experience:
  - phone shows a generic PDF card with an Open button;
  - opening the PDF can leave the installed app and return to the phone home screen;
  - returning to the app may restart at the parent home page instead of the task.
- The attempted PDF.js/canvas path was reverted because it failed real usage:
  - desktop showed `PDF preview needs a retry`;
  - phone/tablet rendering was cramped;
  - one local PDF stayed on `Drawing pages...` for 10+ minutes.
- The app already has a full-screen attachment viewer for tasks. The missing piece is the document rendering engine inside that viewer.

## Implementation Evidence

Local implementation on 2026-06-22:

- Added `config/document-viewer.php` with `DOCUMENT_VIEWER_PDF_PROVIDER=google|native` and `DOCUMENT_VIEWER_OFFICE_PROVIDER=microsoft|download` support.
- Added `App\Services\Library\DocumentViewerUrlFactory`.
- Local loopback storage URLs such as `http://127.0.0.1:8014/storage/...` automatically use native PDF preview because Google/Microsoft cannot fetch localhost from their servers.
- Updated `TaskAttachmentPresenter` so authorized protected task attachments expose:
  - `public_url`;
  - `viewer_provider`;
  - `viewer_url`;
  - existing protected `content_url` and `download_url`.
- Updated student and teacher `AttachmentStudyViewer` components to recognize Office files and key viewer reloads by provider URL.
- Updated the shared full-screen task attachment viewer Blade so PDF/Office documents render inside the existing app shell through Google/Microsoft iframe URLs, with Retry/Open/Download fallback actions.
- Preserved the old native PDF iframe path for legacy same-origin file wrappers and for `DOCUMENT_VIEWER_PDF_PROVIDER=native`.
- Focused verification passed:
  - `php -l app/Services/Library/DocumentViewerUrlFactory.php`
  - `php -l app/Services/Library/TaskAttachmentPresenter.php`
  - `php -l app/Livewire/Student/AttachmentStudyViewer.php`
  - `php -l app/Livewire/Teacher/AttachmentStudyViewer.php`
  - `php artisan test tests/Unit/TaskAttachmentPresenterTest.php`
  - `php artisan test tests/Feature/CoreLms/LifecycleGateTest.php --filter=attachment_study_viewer`

2026-06-22 follow-up:

- Confirmed local `APP_URL=http://127.0.0.1:8014`; Google returned `No preview available` because it cannot fetch loopback URLs.
- Added loopback detection so local desktop testing keeps the working native preview while production HTTPS still uses Google/Microsoft.
- Re-ran the focused presenter and attachment viewer tests successfully.

Remaining proof:

- Production HTTPS code deploy was authorized and executed on 2026-06-22.
- Deployed files:
  - `app/Services/Library/DocumentViewerUrlFactory.php`
  - `config/document-viewer.php`
  - `app/Services/Library/TaskAttachmentPresenter.php`
  - `app/Livewire/Student/AttachmentStudyViewer.php`
  - `app/Livewire/Teacher/AttachmentStudyViewer.php`
  - `resources/views/livewire/student/attachment-study-viewer.blade.php`
  - `favicon.ico`
  - `public/favicon.ico`
  - `public/assets/img/favicon/favicon.ico`
- Remote backup before overwrite:
  - `/home/u504065335/tq9-doc-viewer-backup-20260621-222445`
- Production cache commands completed:
  - `artisan optimize:clear`
  - `artisan config:cache`
  - `artisan view:cache`
  - `artisan route:cache`
- Production favicon checks passed:
  - `https://app.toquran.org/favicon.ico` returned `200`, `image/x-icon`, `21086` bytes.
  - `https://app.toquran.org/assets/img/favicon/favicon.ico` returned `200`, `image/x-icon`, `21086` bytes.
- Production PDF storage/gview smoke passed for a real demo task snapshot:
  - task attachment path: `attachments/general-library-resource-181/79946e8a-65ac-4396-9211-93cacc86303d.pdf`
  - `https://app.toquran.org/storage/attachments/general-library-resource-181/79946e8a-65ac-4396-9211-93cacc86303d.pdf` returned `200`, `application/pdf`, `176914` bytes.
  - Google gview URL for that file returned `200` with title `79946e8a-65ac-4396-9211-93cacc86303d.pdf`.
- Production Library source files are stored on Laravel `local` disk under `storage/app/private/general-library-resources`; this is acceptable for source custody because task delivery uses public attachment snapshots under `storage/app/public/attachments/...`.
- Office-file production smoke remains pending because no Word/PowerPoint/Excel Library resources were found in production at the time of this deploy.
- Owner visual phone/PWA smoke is still required because HTTP `200` from Google does not prove the embedded iframe is pleasant on the actual device.

## Week14 Reuse Requirement

Before implementing, inspect `D:\xampp\htdocs\week14-app-lms` because the owner remembers Google/Microsoft-style document viewing was implemented there.

Inspect at least:

- routes/controllers that serve protected attachment files;
- task attachment viewer Blade/Livewire components;
- any Google Docs Viewer, Google gview, Microsoft Office Online, or viewer proxy logic;
- any signed URL, temporary URL, or public proxy implementation;
- tests around protected files, task attachments, Library attachments, and Office/PDF viewing.

Classify the Week14 approach as:

- copy mostly as-is;
- adapt to To Quran routes/storage;
- use only as reference;
- reject because it exposes files too broadly or has poor UX.

## Viewer Strategy

Use a provider-based viewer resolver:

- PDF:
  - use Google viewer everywhere in the first implementation;
  - keep native iframe only as an explicit debugging/rollback option through config.
- Office documents:
  - use Microsoft Office Online viewer for `.doc`, `.docx`, `.ppt`, `.pptx`, `.xls`, `.xlsx`.
- Images/audio/video:
  - keep current first-party rendering.
- YouTube/external links:
  - keep current behavior.
- Unknown files:
  - show Open/Download fallback inside the app shell.

## Viewer File Access Design

Google and Microsoft viewers cannot read Laravel-authenticated private routes unless they can fetch a URL themselves.

Current code evidence:

- task and Library uploaded files are stored on Laravel's `public` disk;
- `public/storage` points to `storage/app/public`;
- many attachment paths are already public-by-path through `APP_URL/storage/{path}` if someone knows the path.
- no global `Content-Security-Policy` is currently blocking embedded `docs.google.com` or `view.officeapps.live.com` frames; if a CSP is added later, it must explicitly allow the selected viewer domains.

Launch decision:

- Use the existing public storage URL for normal teaching materials when embedding Google/Microsoft viewers.
- App permissions still protect discovery and normal navigation: users must be authorized to see the task/Library item before the app renders the viewer URL.
- Do not pretend this gives full private-file secrecy. It does not.
- Do not move all existing attachments to a private disk during launch; that would be a larger, riskier storage migration.
- Do not make Library folders browsable or expose file indexes.
- If a future document category needs true privacy, create a separate private-document plan with private storage, short-lived signed routes, and stricter file policy.

Tradeoff to document:

- Google/Microsoft must fetch the file without the user's Laravel session cookie.
- Owner decision: for normal teaching materials, opening reliably inside the app viewer is more important than full private-file protection.
- Public-by-path file URLs are acceptable for normal To Quran task/Library teaching materials such as Tajweed pages.
- Highly private documents, if ever added later, should use a separate stricter policy.

## To Quran-Specific Changes

Likely implementation areas:

- Add a document viewer URL resolver service, for example:
  - `App\Services\Library\DocumentViewerUrlFactory`
  - or a task-attachment-specific equivalent if existing attachment code has a better home.
- Generate external viewer URLs from the authorized app payload, using the existing public storage URL for viewer-fetchable file types.
- Use exact provider endpoints:
  - Google PDF viewer: `https://docs.google.com/gview?embedded=true&url={encodedPublicFileUrl}`
  - Microsoft Office viewer: `https://view.officeapps.live.com/op/embed.aspx?src={encodedPublicFileUrl}`
- Build `{encodedPublicFileUrl}` from a canonical public file URL whose path is safe for Arabic, spaces, and other non-ASCII filenames:
  - percent-encode path segments correctly before passing the URL to Google/Microsoft;
  - then encode the full public URL as the provider query parameter;
  - do not expose local filesystem paths and do not accidentally double-decode or double-encode stored paths.
- Update `AttachmentStudyViewer` payloads to include:
  - `viewer_provider` such as `native`, `google`, `microsoft`, or `download`;
  - `viewer_url`;
  - `download_url`;
  - `extension`;
  - safe display title.
- Update `resources/views/livewire/student/attachment-study-viewer.blade.php`:
  - keep one app shell;
  - use provider iframe only inside the content area;
  - remove custom document-page navigation controls from the app shell;
  - keep only task-attachment previous/next controls in the app header;
  - let Google/Microsoft own document-page navigation inside the embedded viewer;
  - on phone, visually separate the app attachment controls from the provider iframe so it does not feel like two competing arrow systems;
  - keep a clear close button that returns to the task page.
- Enumerate the actual viewer surfaces before wiring the resolver. At minimum, check whether the app has separate student, teacher, parent, support, admin, and Library preview components, then wire only the surfaces that really render document previews.
- Review teacher-side session/task viewer too, not only student-side, because teachers need the same reliable document preview.
- Review Library folder/resource preview surfaces too, especially the uploaded `Tajweed Beginner's Book` resources, because the same PDFs/videos must be usable before and after attachment to a task.
- Ensure parent/support views that open student task attachments reuse the same resolver where applicable, without creating duplicate viewer implementations.

Provider-selection rule:

- For first implementation, use Google viewer for PDFs in task and Library document preview contexts across student, parent, support, teacher, and admin surfaces where those previews exist.
- Prefer a simple, explicit provider decision over fragile device guessing.
- First implementation default: use Google viewer for PDFs in task/Library document preview everywhere, including desktop, unless `DOCUMENT_VIEWER_PDF_PROVIDER=native` is set for debugging or rollback.
- Because native desktop PDF iframe is currently the known-good desktop path, keep `DOCUMENT_VIEWER_PDF_PROVIDER=native` as a one-step rollback that does not require code edits or redeploying assets.
- Office documents always use Microsoft viewer where the file is externally fetchable through the viewer URL.
- Add a small config/env override for production debugging: `DOCUMENT_VIEWER_PDF_PROVIDER=google|native` and `DOCUMENT_VIEWER_OFFICE_PROVIDER=microsoft|download`.

## Mobile UX Requirements

- Phone document viewer must stay inside the To Quran full-screen viewer.
- The student must be able to close the document and return to the task/session view.
- No duplicate app-rendered document arrows: the app header may move between task attachments, while Google/Microsoft handle pages/slides/sheets inside the iframe.
- Header text must not be huge or clipped on phone.
- Arabic/PDF filenames must display safely with ellipsis.
- Cross-origin Google/Microsoft iframe errors cannot be reliably inspected from the app. The app should not promise perfect error detection.
- Show always-available fallback actions near the viewer, and optionally show a timeout hint if the iframe appears to be loading too long:
  - Retry;
  - Open in new tab;
  - Download.
- The fallback should explain the issue in child/parent-friendly language, not technical wording.

## DB Impact

No schema change is expected.

Possible non-schema data/config impact:

- None for existing files if URLs are generated dynamically.
- If true private-document support is later required and needs a token table/cache, create a separate mini-plan before adding any DB artifact.

## Public Website Handoff

No public website code change is expected.

Public website relevance:

- Do not advertise in-browser document viewing until production smoke confirms document previews work.
- If public marketing later mentions downloadable worksheets/books/videos, make sure the claim matches app behavior.

## Test Scope

Automated tests:

- PDF attachment resolves to Google viewer by default, and native only when forced by the provider config override.
- Office attachment resolves to Microsoft viewer.
- Image/video/audio behavior is unchanged.
- Unknown file types still show fallback.
- Provider URLs are generated only after the current user is authorized to view the task/Library item.
- Viewer URLs use existing public storage URLs and do not expose local filesystem paths.
- Missing files fall back cleanly.
- Attachment viewer keeps one app-level navigation shell.
- Existing protected-file authorization tests continue to pass.

Manual/local tests:

- Local tests cannot prove Google/Microsoft render the document because external viewers cannot fetch `127.0.0.1`; local checks should verify URL generation, provider selection, iframe embedding, and fallback UI only.
- Desktop Chrome:
  - student task PDF;
  - teacher task PDF;
  - Office file if a fixture exists.
- Phone viewport:
  - PDF opens inside the full-screen viewer;
  - close returns to the task;
  - app does not jump to home screen.
- Tablet/iPad viewport:
  - PDF uses Google viewer and stays inside the full-screen viewer.

Production smoke:

- Use a real production HTTPS URL because Google/Microsoft cannot fetch `127.0.0.1`.
- Before testing viewers, verify production URL/storage assumptions:
  - `APP_URL=https://app.toquran.org`;
  - `STORAGE_URL`, if configured, also points to the public app domain;
  - `public/storage` exists and points to `storage/app/public`;
  - a known attachment URL under `https://app.toquran.org/storage/...` returns `200` without exposing a local filesystem path;
  - at least one attachment URL with an Arabic name, spaces, or other non-ASCII characters returns `200` when encoded the same way the viewer resolver encodes it.
- Test one PDF from `Tajweed Beginner's Book`.
- Test one Office file if available; otherwise upload a small safe test Office file to the Library and attach it to a demo task.
- Confirm no raw local/private filesystem path is visible.
- Confirm the visible file URL is an expected `https://app.toquran.org/storage/...` style URL if inspected.
- If Google `gview` fails production smoke on the Tajweed PDFs with "No preview available" or equivalent, stop and choose a different PDF strategy instead of continuing to patch around it.

## Non-Goals

- Do not build a custom PDF renderer now.
- Do not add Google Drive integration.
- Do not upload files to Google Drive or Microsoft storage.
- Do not migrate storage disks or make Library folders browsable.
- Do not rebuild the whole task viewer UI.
- Do not change task/session data models unless Week14 inspection proves it is necessary and a separate DB-safe plan is created.

## Stop Conditions

- Stop if Google PDF viewer fails production smoke on the real Tajweed PDFs.
- Stop if Microsoft Office viewer fails production smoke on a real Office file and no acceptable fallback exists.
- Stop if implementation exposes file indexes, folder browsing, local filesystem paths, or unrelated files.
- Stop if the viewer works locally only because of `localhost` assumptions that cannot work on production HTTPS.
- Stop if phone testing still leaves the installed app or loses the task return path.
- Stop if Week14 already solved this differently and safely; reassess before duplicating work.
