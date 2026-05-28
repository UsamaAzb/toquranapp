-- To Quran starter/reference data execution note
-- Date: 2026-05-28
-- Target: real app DB name, `u504065335_to_quran`
-- Patch executed: database/manual/patches/2026-05-28-toquran-starter-reference-data.sql
-- Status: executed locally against XAMPP MySQL after the real-target schema baseline.

-- Preflight:
-- - Verified DATABASE() = 'u504065335_to_quran'.
-- - Verified app schema baseline was present before insert work.

-- Rows created intentionally:
-- - roles: 7 (`owner`, `super_admin`, `admin`, `customer_support`, `teacher`, `parent`, `student`)
-- - services_types: 5
-- - services: 5
-- - academic_years: 1 current row (`2026-2027`)
-- - school_program: 1 (`To Quran Private Tutoring`)
-- - subjects: 3 (`Quran Memorization`, `Quranic Arabic`, `My Deen Journey`)
-- - grade_levels: 4 (`General Learner`, `Beginner`, `Intermediate`, `Advanced`)
-- - grade_level_subjects: 12

-- Rows deliberately not created:
-- - users/admin/teacher/parent/student accounts
-- - Week14 QA/test accounts
-- - Week14 English/Cambridge/phonics content
-- - Quran video Library rows

-- App adaptation paired with this patch:
-- - `App\Support\BookingSubjectProvisioning` now maps subject ids 1, 2, and 15 to To Quran names.
-- - active-by-default subjects are Quran Memorization, Quranic Arabic, and My Deen Journey.
-- - legacy Week14 constants remain as deprecated aliases for compatibility while the app is adapted.

-- Verification:
-- - `php -l app\Support\BookingSubjectProvisioning.php` passed.
-- - Focused auth/PWA/credential tests passed after real-target setup: 13 tests, 50 assertions.
-- - Tinker check:
--   - `AcademicYear::currentId()` returned 1.
--   - `BookingSubjectProvisioning::planForGradeLevel(1)` returned Quran Memorization, Quranic Arabic, and My Deen Journey with active student/teacher statuses.

-- Test caveat:
-- - Broader Week14 booking transfer tests still require To Quran adaptation.
-- - The first failures were a missing local Vite manifest for Livewire booking views, followed by Week14 service fixture mismatches.
-- - This does not change the starter/reference data result; it is TQ2 follow-up work.
