-- To Quran learning catalog reference data execution note
-- Date: 2026-05-29
-- Target: `u504065335_to_quran`
-- Patch: database/manual/patches/2026-05-29-toquran-learning-catalog-reference-data.sql
-- Backup before execution: database/manual/backups/2026-05-29-114724-u504065335_to_quran-before-learning-catalog.sql
-- Status: executed locally against the real To Quran app DB target.

-- Reason:
-- The first starter/reference data pass created only Quran Memorization, Quranic Arabic,
-- and My Deen Journey as active LMS subjects. Owner clarified that the LMS class-subject
-- catalog is:
-- - Quran Memorization
-- - Arabic Language
-- - Quranic Arabic
-- - Sanad Program
-- - My Deen Journey / MDJ
-- - Well Being
--
-- MDJ and Well Being must stay separate. Parent-written behavior points resolve to
-- Well Being only through ParentBehaviorSubjectResolver.

-- Execution:
-- 1. Confirmed Laravel target DB was `u504065335_to_quran`.
-- 2. Created pre-change backup:
--    database/manual/backups/2026-05-29-114724-u504065335_to_quran-before-learning-catalog.sql
-- 3. Ran the guarded patch with:
--    SET @toquran_confirm_real_db_target = 'u504065335_to_quran';
-- 4. Re-ran the patch with the confirmation variable to verify idempotency.
-- 5. Ran the patch without the confirmation variable to verify the expected guard failure.

-- Result:
-- `subjects` now has 6 active/current To Quran LMS class subjects:
-- - id 1: Quran Memorization / QURAN_MEM
-- - id 2: Quranic Arabic / QURAN_AR
-- - id 3: Arabic Language / ARABIC_LANG
-- - id 4: Sanad Program / SANAD
-- - id 15: My Deen Journey / MDJ
-- - id 16: Well Being / WELL_BEING

-- `grade_level_subjects` now has 24 active mappings:
-- - 4 learner levels x 6 To Quran LMS class subjects.

-- App-code verification:
-- - BookingSubjectProvisioning maps the 6 subject IDs to the To Quran catalog.
-- - Active-by-default subjects are Quran Memorization, Quranic Arabic, My Deen Journey, and Well Being.
-- - Arabic Language and Sanad Program are present in the catalog but provision inactive student/teacher access by default until assigned.

-- Focused test verification:
-- php artisan test tests/Feature/BookingTransferLifecycleInitTest.php tests/Feature/BookingTransferGatingTest.php tests/Feature/BookingMilestoneTest.php tests/Unit/BookingServiceInterestTest.php
--
-- Result:
-- - 86 passed
-- - 455 assertions

-- No user, family, teacher assignment, public service, or Week14 school-subject activation rows were changed by this patch.
