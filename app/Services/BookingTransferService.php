<?php

namespace App\Services;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Models\AcademicYear;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingChildEmail;
use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\GradeLevel;
use App\Models\ParentModel;
use App\Models\PunishmentAgreement;
use App\Models\PunishmentsSuggestion;
use App\Models\RewardDisciplinePoint;
use App\Models\RewardDisciplineTransfer;
use App\Models\Services_type;
use App\Models\Student;
use App\Models\StudentClassesHistory;
use App\Models\StudentGift;
use App\Models\StudentsSubject;
use App\Models\TaskPinHash;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Support\BookingServiceInterest;
use App\Support\BookingSubjectProvisioning;
use App\Support\BookingTransferReadiness;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class BookingTransferService
{
    public function __construct(
        private readonly BookingParentIdentityResolver $resolver,
    ) {}

    public function transferChild(BookingChild $child): array
    {
        $child->loadMissing('booking');

        $result = DB::transaction(function () use ($child) {
            $lockedChild = BookingChild::query()
                ->with('booking')
                ->lockForUpdate()
                ->findOrFail($child->id);

            $booking = $lockedChild->booking;

            if (! $booking) {
                throw new InvalidArgumentException('Child row is not linked to a booking.');
            }

            if (! $booking->parent_name || ! $lockedChild->child_name) {
                throw new InvalidArgumentException('Booking is missing required data to transfer.');
            }

            if ($lockedChild->transfer_status === 'transferred' && $lockedChild->student_id) {
                throw new InvalidArgumentException('This child has already been transferred.');
            }

            if (! $lockedChild->isFitReadyForTransfer()) {
                throw new InvalidArgumentException('Only fit-ready children can be transferred.');
            }

            if ($blockedReason = BookingTransferReadiness::blockedReason($lockedChild, $booking, false)) {
                throw new InvalidArgumentException($blockedReason);
            }

            $serviceType = $this->resolvePrimaryServiceType($lockedChild, $booking);
            [$parentFirst, $parentLast] = array_pad(explode(' ', trim($booking->parent_name), 2), 2, null);
            $resolution = $this->resolver->resolveTransferTarget($lockedChild);

            if (! ($resolution['allowed'] ?? false)) {
                if (($resolution['outcome'] ?? null) === 'blocked_conflict') {
                    $this->resolver->recordResolution($resolution['audit_payload'] ?? []);
                }

                return [
                    'identity_blocked' => true,
                    'blocked_reason' => $resolution['blocked_reason'] ?? 'Parent identity must be reconciled before transfer.',
                ];
            }

            $parent = $this->resolveParentForTransfer($lockedChild, $booking, $parentFirst, $parentLast, $resolution);
            $familyStatus = $this->initializeFamilyLifecycle($parent, $resolution);
            $shouldSyncParentUserStatus = $this->shouldInitializeParentUserStatus($resolution);

            $gradeLevelId = BookingTransferReadiness::effectiveGradeLevelId($lockedChild, $booking);
            $gradeLevel = $gradeLevelId ? GradeLevel::find($gradeLevelId) : null;
            $programId = $gradeLevel?->program_id;
            [$student, $studentWasExisting] = $this->resolveStudentForTransfer($parent, $lockedChild);

            $student->fill([
                'parent_id' => $parent->id,
                'first_name' => $lockedChild->child_name,
                'age' => $lockedChild->child_age,
                'grade_level_id' => $gradeLevelId,
                'program_id' => $programId,
                'current_school' => $lockedChild->current_school ?: $booking->current_school,
                'school_system' => BookingTransferReadiness::effectiveSchoolSystem($lockedChild, $booking),
                'service_type_id' => $serviceType->id,
            ]);

            $student->save();

            if ($this->shouldInitializeChildLifecycle($student, $studentWasExisting)) {
                $this->initializeChildLifecycle($student);
            }

            $this->ensureStudentHasClass($student, $lockedChild, $booking);
            $parentUserResult = $this->createOrGetUserForParent($parent, $shouldSyncParentUserStatus);
            $studentUserResult = $this->createOrGetUserForStudent($student);
            $this->syncLifecycleUserStatuses($parent, $student, $shouldSyncParentUserStatus);
            $this->seedTenDefaultGifts($student->id);
            $this->seedBehaviors($student->id);

            $lockedChild->student_id = $student->id;
            $lockedChild->transfer_status = 'transferred';
            $lockedChild->save();

            $booking->parent_id = $parent->id;

            if ($this->isLegacyPrimaryChild($lockedChild)) {
                // Legacy compatibility only until booking-level transfer fields are retired.
                $booking->transfer = 1;
                $booking->student_id = $student->id;
            }

            $booking->save();
            $this->recordChildTransferredIntoFamily($parent, $student, $lockedChild, $booking, $familyStatus);

            return [
                'booking_id' => $booking->id,
                'child_id' => $lockedChild->id,
                'parent_id' => $parent->id,
                'student_id' => $student->id,
                'transfer_status' => $lockedChild->transfer_status,
                'parent_user_id' => $parentUserResult['user']->id ?? null,
                'student_user_id' => $studentUserResult['user']->id ?? null,
                'parent_username' => $parentUserResult['username'] ?? null,
                'student_username' => $studentUserResult['username'] ?? null,
                'family_workspace_url' => route('admin.families.show', $parent),
            ];
        });

        if (($result['identity_blocked'] ?? false) === true) {
            throw new InvalidArgumentException($result['blocked_reason']);
        }

        return $result;
    }

    public function confirmLinkedParentContactUpdate(BookingChild $child, string $note): array
    {
        if (trim($note) === '') {
            throw new InvalidArgumentException('A note is required before updating the linked parent contact.');
        }

        $child->loadMissing('booking');

        return DB::transaction(function () use ($child, $note): array {
            $lockedChild = BookingChild::query()
                ->with('booking')
                ->lockForUpdate()
                ->findOrFail($child->id);
            $booking = $lockedChild->booking;

            if (! $booking || ! $booking->parent_id) {
                throw new InvalidArgumentException('Linked parent contact update requires a booking with parent_id.');
            }

            $resolution = $this->resolver->resolveTransferTarget($lockedChild);

            if (($resolution['outcome'] ?? null) !== 'update_linked_parent_contact') {
                throw new InvalidArgumentException($resolution['blocked_reason'] ?? 'No linked parent contact update is currently required.');
            }

            $parent = ParentModel::query()
                ->lockForUpdate()
                ->findOrFail($booking->parent_id);

            $parentUser = $parent->user_id
                ? User::query()->lockForUpdate()->find($parent->user_id)
                : null;

            $updatedEmail = $this->normalizedOptionalContact($booking->parent_email);
            $updatedPhone = $this->normalizedOptionalContact($booking->parent_phone);

            $this->assertLinkedParentContactUpdateIsUnique($parent, $parentUser, $updatedEmail, $updatedPhone);

            if ($updatedEmail !== null) {
                $parent->email = $updatedEmail;
            }

            if ($updatedPhone !== null) {
                $parent->phone = $updatedPhone;
            }

            $parent->save();

            if ($parentUser) {
                if ($updatedEmail !== null) {
                    $parentUser->email = $updatedEmail;
                }

                if ($updatedPhone !== null) {
                    $parentUser->phone = $updatedPhone;
                }

                $parentUser->save();
            }

            $this->resolver->recordResolution(array_merge($resolution['audit_payload'] ?? [], [
                'target_parent_id' => $parent->id,
                'resolved_parent_email' => $parent->email,
                'resolved_parent_phone' => $parent->phone,
                'resolution_note' => $note,
            ]));

            return [
                'booking_id' => $booking->id,
                'child_id' => $lockedChild->id,
                'parent_id' => $parent->id,
            ];
        });
    }

    protected function resolveParentForTransfer(BookingChild $child, Booking $booking, ?string $parentFirst, ?string $parentLast, ?array $resolution = null): ParentModel
    {
        $resolution ??= $this->resolver->resolveTransferTarget($child);

        if (! ($resolution['allowed'] ?? false)) {
            throw new InvalidArgumentException($resolution['blocked_reason'] ?? 'Parent identity must be reconciled before transfer.');
        }

        if (($resolution['outcome'] ?? null) === 'create_new_parent') {
            $parent = ParentModel::create([
                'first_name' => $parentFirst ?: 'Parent',
                'last_name' => $parentLast,
                'email' => $booking->parent_email,
                'phone' => $booking->parent_phone,
            ]);

            $this->resolver->recordResolution(array_merge($resolution['audit_payload'] ?? [], [
                'target_parent_id' => $parent->id,
                'resolved_parent_email' => $parent->email,
                'resolved_parent_phone' => $parent->phone,
            ]));

            return $parent;
        }

        $parent = ParentModel::query()
            ->lockForUpdate()
            ->find($resolution['target_parent_id'] ?? null);

        if (! $parent) {
            throw new InvalidArgumentException('Resolved parent account could not be loaded for transfer.');
        }

        $this->assertExistingParentHasValidLifecycleStatus($parent);

        if (! $parent->first_name && $parentFirst) {
            $parent->first_name = $parentFirst;
        }

        if (! $parent->last_name && $parentLast) {
            $parent->last_name = $parentLast;
        }

        if (! $parent->email && $booking->parent_email) {
            $parent->email = $booking->parent_email;
        }

        if (! $parent->phone && $booking->parent_phone) {
            $parent->phone = $booking->parent_phone;
        }

        $parent->save();

        $this->resolver->recordResolution(array_merge($resolution['audit_payload'] ?? [], [
            'target_parent_id' => $parent->id,
            'resolved_parent_email' => $parent->email,
            'resolved_parent_phone' => $parent->phone,
        ]));

        return $parent;
    }

    /**
     * @deprecated Sprint 4 retires transfer-time parent/admin emails. Use activation email jobs from the Family Workspace.
     */
    public function sendTransferWelcomeEmail(BookingChild $child, bool $isResend = false): void
    {
        unset($child, $isResend);
    }

    /**
     * @deprecated Sprint 4 retires transfer-time parent/admin emails. Use activation email jobs from the Family Workspace.
     */
    public function sendTransferAdminEmail(BookingChild $child, bool $isResend = false): void
    {
        unset($child, $isResend);
    }

    private function assertLinkedParentContactUpdateIsUnique(
        ParentModel $parent,
        ?User $user,
        ?string $email,
        ?string $phone
    ): void {
        $validator = Validator::make(
            [
                'email' => $email,
                'phone' => $phone,
            ],
            [
                'email' => [
                    'nullable',
                    'email',
                    'max:190',
                    Rule::unique('parents', 'email')->ignore($parent->id),
                    Rule::unique('users', 'email')->ignore($user?->id),
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                    Rule::unique('parents', 'phone')->ignore($parent->id),
                    Rule::unique('users', 'phone')->ignore($user?->id),
                ],
            ],
            [
                'email.email' => 'Submitted parent email must be a valid email address.',
                'email.unique' => 'Submitted parent email is already used by another family account.',
                'phone.unique' => 'Submitted parent phone is already used by another family account.',
            ]
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    private function normalizedOptionalContact(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    protected function resolvePrimaryServiceType(BookingChild $child, Booking $booking): Services_type
    {
        $serviceValue = collect($child->service_interests ?? [])
            ->map(fn ($value) => BookingServiceInterest::normalize($value))
            ->filter()
            ->first();

        if (! $serviceValue && $booking->service_interest) {
            $serviceValue = collect(explode(',', $booking->service_interest))
                ->map(fn ($value) => trim($value))
                ->filter()
                ->map(fn ($value) => BookingServiceInterest::normalize($value))
                ->first();
        }

        if (! $serviceValue) {
            throw new InvalidArgumentException('Child transfer requires at least one service interest.');
        }

        $serviceType = Services_type::where('value', $serviceValue)->first();

        if (! $serviceType) {
            throw new InvalidArgumentException("No matching service type was found for '{$serviceValue}'.");
        }

        return $serviceType;
    }

    protected function isLegacyPrimaryChild(BookingChild $child): bool
    {
        $firstChildId = $child->booking->children()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');

        return (int) $firstChildId === (int) $child->id;
    }

    protected function ensureStudentHasClass(Student $student, BookingChild $child, Booking $booking): ?ClassModel
    {
        $academicYearId = $this->currentAcademicYearId();
        $gradeLevelId = BookingTransferReadiness::effectiveGradeLevelId($child, $booking);

        if (! $gradeLevelId) {
            throw new InvalidArgumentException('Child transfer requires a grade level.');
        }

        $subjectPlan = BookingSubjectProvisioning::planForGradeLevel($gradeLevelId);

        if ($subjectPlan === []) {
            throw new InvalidArgumentException('Child transfer requires grade-level subjects for the selected grade.');
        }

        $gradeName = null;
        if ($gradeLevelId) {
            $gradeLevel = GradeLevel::find($gradeLevelId);
            $gradeName = $gradeLevel?->title;
        }

        $title = trim($student->first_name.($gradeName ? ' - '.$gradeName : ''));

        $currentHistory = StudentClassesHistory::query()
            ->where('student_id', $student->id)
            ->where('status', 'current')
            ->orderByDesc('id')
            ->first();
        $currentClass = $student->current_class_id
            ? ClassModel::find($student->current_class_id)
            : null;

        if (! $currentClass && $currentHistory) {
            $currentClass = ClassModel::find($currentHistory->class_id);
        }

        if ($currentClass && (int) $currentClass->grade_level_id === (int) $gradeLevelId) {
            if ((int) $student->current_class_id !== (int) $currentClass->id) {
                $student->current_class_id = $currentClass->id;
                $student->save();
            }

            $this->syncStudentSubjectPlan($student, $currentClass, $gradeLevelId, $gradeName, $subjectPlan);

            return $currentClass;
        }

        $class = ClassModel::firstOrCreate(
            [
                'title' => $title,
                'grade_level_id' => $gradeLevelId,
            ],
            [
                'grade_level_id' => $gradeLevelId,
                'academic_year_id' => $academicYearId,
            ]
        );

        $today = now()->toDateString();

        if ($currentHistory && (int) $currentHistory->class_id !== (int) $class->id) {
            $currentHistory->status = 'past';
            $currentHistory->to_date = $currentHistory->to_date ?: $today;
            $currentHistory->save();

            StudentClassesHistory::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'status' => 'current',
                'from_date' => $today,
            ]);
        } elseif (! $currentHistory) {
            StudentClassesHistory::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'status' => 'current',
                'from_date' => $today,
            ]);
        }

        $student->current_class_id = $class->id;
        $student->save();

        $this->syncStudentSubjectPlan($student, $class, $gradeLevelId, $gradeName, $subjectPlan);

        return $class;
    }

    protected function syncStudentSubjectPlan(
        Student $student,
        ClassModel $class,
        int $gradeLevelId,
        ?string $gradeName,
        array $subjectPlan
    ): void {
        $academicYearId = $this->currentAcademicYearId();

        foreach ($subjectPlan as $subject) {
            $classSubject = ClassSubject::firstOrCreate([
                'grade_level_subject_id' => $subject['grade_level_subject_id'],
                'class_id' => $class->id,
            ]);

            $studentSubject = StudentsSubject::firstOrCreate(
                [
                    'grade_level_subject_id' => $subject['grade_level_subject_id'],
                    'student_id' => $student->id,
                ],
                [
                    'academic_year_id' => $academicYearId,
                    'status' => $subject['student_status'],
                    'enrolled_at' => now()->toDateString(),
                    'class_subject_id' => $classSubject->id,
                ]
            );

            if ((int) $studentSubject->class_subject_id !== (int) $classSubject->id) {
                $studentSubject->class_subject_id = $classSubject->id;
                $studentSubject->save();
            }

            TeacherSubjectClass::firstOrCreate(
                [
                    'user_teacher_coteacher_id' => 3,
                    'class_id' => $class->id,
                    'subject_id' => $subject['subject_id'],
                    'class_subject_id' => $classSubject->id,
                ],
                [
                    'teacher_name' => 'Dr.Osama',
                    'grade_id' => $gradeLevelId,
                    'grade_name' => $gradeName,
                    'class_name' => $class->title,
                    'subject_name' => $subject['subject_name'],
                    'status' => $subject['teacher_status'],
                    'assigned_at' => now()->toDateString(),
                ]
            );

            BookingSubjectProvisioning::syncTeacherSubjectClassStatus($classSubject->id);
        }
    }

    protected function createOrGetUserForParent(ParentModel $parent, bool $syncExistingUserStatus): array
    {
        $user = null;

        if ($parent->email) {
            $user = User::where('email', $parent->email)->first();
        }
        if (! $user && $parent->phone) {
            $user = User::where('phone', $parent->phone)->first();
        }

        $username = null;

        if ($user) {
            $username = $user->name;

            if ($syncExistingUserStatus) {
                $user->status = $this->familyUserStatus($parent);
                $user->save();
            }

            if (Schema::hasColumn($parent->getTable(), 'user_id') && ! $parent->user_id) {
                $parent->user_id = $user->id;
                $parent->user_name = $user->name;
                $parent->save();
            }

            return ['user' => $user, 'username' => $username ?: ($user->username ?? null)];
        }

        $username = $parent->first_name.'_PT'.rand(1000, 9999);
        $plain = app(CredentialService::class)->generateParentPasswordForName(
            $parent->first_name ?: 'Parent'
        );

        $user = User::create([
            'name' => $username,
            'first_name' => trim((string) ($parent->first_name ?? '')),
            'last_name' => trim((string) ($parent->last_name ?? '')),
            'password' => Hash::make($plain),
            'email' => $parent->email,
            'phone' => $parent->phone,
            'status' => $this->familyUserStatus($parent),
            'recoverable_password_encrypted' => $plain,
        ]);
        $user->assignRole('parent');

        if (Schema::hasColumn($parent->getTable(), 'user_id') && ! $parent->user_id) {
            $parent->user_id = $user->id;
            $parent->user_name = $user->name;
            $parent->save();
        }

        return ['user' => $user, 'username' => $username ?: ($user->username ?? null)];
    }

    protected function createOrGetUserForStudent(Student $student): array
    {
        $user = null;
        if (Schema::hasColumn($student->getTable(), 'user_id') && $student->user_id) {
            $user = User::find($student->user_id);
        }

        $username = null;

        if ($user) {
            $username = $user->name;
            $user->status = $this->childUserStatus($student);
            $user->save();

            return ['user' => $user, 'username' => $username ?: ($user->username ?? null)];
        }

        $username = $this->generateChildUsername($student);
        $plain = app(CredentialService::class)->generateChildPassword();

        $studentEmail = $username.'@app.toquran.org';
        $user = User::create([
            'name' => $username,
            'first_name' => trim((string) ($student->first_name ?? '')),
            'last_name' => trim((string) ($student->last_name ?? '')),
            'password' => Hash::make($plain),
            'email' => $studentEmail,
            'phone' => $student->student_phone,
            'status' => $this->childUserStatus($student),
            'recoverable_password_encrypted' => $plain,
        ]);

        $user->assignRole('student');

        if (Schema::hasColumn($student->getTable(), 'user_id') && ! $student->user_id) {
            $student->user_id = $user->id;
            $student->user_name = $user->name;
            $student->student_email = $user->email;
            $student->save();
        }

        return ['user' => $user, 'username' => $username ?: ($user->username ?? null)];
    }

    protected function seedTenDefaultGifts(int $studentId): array
    {
        $academicYearId = $this->currentAcademicYearId();

        for ($i = 1; $i <= 10; $i++) {
            $points = 100 * $i;

            StudentGift::firstOrCreate(
                [
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'points_required' => $points,
                ],
                [
                    'gift_id' => null,
                    'gift_name' => "Reward{$i}",
                    'gift_image' => null,
                    'status' => $i === 1 ? StudentGift::STATUS_PENDING : StudentGift::STATUS_WAITING,
                    'approved_by_id' => null,
                    'approved_by_name' => null,
                    'approval_timestamp' => null,
                    'redeemed_at' => null,
                ]
            );
        }

        $student = Student::find($studentId);
        $userId = $student?->user_id;
        $pin = '1414';

        if ($userId && ! TaskPinHash::firstWhere('user_id', $userId)) {
            TaskPinHash::create([
                'user_id' => $userId,
                'pin_unhash' => $pin,
                'pin_hash' => Hash::make($pin),
            ]);
        }

        return ['ok' => true, 'message' => 'Created 10 default gifts for the student.'];
    }

    private function currentAcademicYearId(): int
    {
        return AcademicYear::currentId();
    }

    protected function resolveTransferEmailContext(BookingChild $child): array
    {
        $child->loadMissing('booking');

        $booking = $child->booking;

        if (! $booking) {
            throw new InvalidArgumentException('Child row is not linked to a booking.');
        }

        $parent = null;
        if ($booking->parent_id) {
            $parent = ParentModel::find($booking->parent_id);
        }

        if (! $parent && $booking->parent_email) {
            $parent = ParentModel::where('email', $booking->parent_email)->first();
        }

        if (! $parent && $booking->parent_phone) {
            $parent = ParentModel::where('phone', $booking->parent_phone)->first();
        }

        $student = $child->student_id ? Student::find($child->student_id) : null;

        if (! $parent || ! $student) {
            throw new InvalidArgumentException('Transfer email context is incomplete.');
        }

        return [$parent, $student, $booking];
    }

    protected function createTransferAttemptRow(BookingChild $child, string $emailType): BookingChildEmail
    {
        return app(BookingChildEmailService::class)->createAttemptRow($child, $emailType);
    }

    protected function markTransferAttemptSent(BookingChildEmail $attempt, bool $isResend): void
    {
        app(BookingChildEmailService::class)->markAttemptSent($attempt, $isResend);
    }

    protected function markTransferAttemptFailed(BookingChildEmail $attempt, string $message): void
    {
        app(BookingChildEmailService::class)->markAttemptFailed($attempt, $message);
    }

    protected function seedBehaviors(int $studentId): string
    {
        $allReward = RewardDisciplineTransfer::all();

        foreach ($allReward as $reward) {
            RewardDisciplinePoint::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'title' => $reward->title,
                    'status' => 'active',
                ],
                [
                    'description' => $reward->description,
                    'type' => $reward->type,
                    'points' => $reward->points,
                    'discipline_icon_id' => $reward->discipline_icon_id,
                    'discipline_icon_path' => $reward->discipline_icon_path,
                    'sort' => $reward->sort,
                    'teacher_desc' => $reward->teacher_desc,
                    'selected' => $reward->selected,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $punishments = PunishmentsSuggestion::all();

        foreach ($punishments as $punishment) {
            PunishmentAgreement::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'punishment_type_id' => $punishment->punishment_type_id,
                    'title' => $punishment->suggestion_text,
                ],
                [
                    'status' => 'active',
                ]
            );
        }

        return 'Behaviors seeded successfully';
    }

    protected function initializeFamilyLifecycle(ParentModel $parent, array $resolution): string
    {
        if (($resolution['outcome'] ?? null) !== 'create_new_parent') {
            $this->assertExistingParentHasValidLifecycleStatus($parent);

            return (string) $parent->lifecycle_status;
        }

        $parent->lifecycle_status = FamilyLifecycleStatus::PendingActivation->value;
        $parent->save();

        return FamilyLifecycleStatus::PendingActivation->value;
    }

    protected function initializeChildLifecycle(Student $student): void
    {
        $student->account_status = ChildAccountStatus::PendingActivation->value;
        $student->save();
    }

    /** @return array{0: Student, 1: bool} */
    protected function resolveStudentForTransfer(ParentModel $parent, BookingChild $child): array
    {
        if ($child->student_id) {
            $student = Student::query()
                ->lockForUpdate()
                ->find($child->student_id);

            if (! $student) {
                throw new InvalidArgumentException('Resolved child record could not be loaded for transfer.');
            }

            if ((int) $student->parent_id !== (int) $parent->id) {
                throw new InvalidArgumentException('Linked child record does not belong to the resolved family.');
            }

            return [$student, true];
        }

        $sameNameStudent = Student::query()
            ->where('parent_id', $parent->id)
            ->get(['id', 'first_name'])
            ->first(fn (Student $student): bool => $this->normalizeChildIdentityName($student->first_name)
                === $this->normalizeChildIdentityName($child->child_name));

        if ($sameNameStudent) {
            throw new InvalidArgumentException(
                'Transfer is blocked until child identity is reconciled for the existing child record.'
            );
        }

        return [new Student, false];
    }

    protected function shouldInitializeChildLifecycle(Student $student, bool $studentWasExisting): bool
    {
        return ! $studentWasExisting
            || (! in_array($student->account_status, $this->childLifecycleStatuses(), true)
                && ! $this->hasLinkedStudentUser($student));
    }

    protected function normalizeChildIdentityName(?string $name): string
    {
        return preg_replace('/\s+/', ' ', Str::lower(trim((string) $name))) ?? '';
    }

    protected function hasLinkedStudentUser(Student $student): bool
    {
        return Schema::hasColumn($student->getTable(), 'user_id')
            && filled($student->user_id);
    }

    protected function shouldInitializeParentUserStatus(array $resolution): bool
    {
        return ($resolution['outcome'] ?? null) === 'create_new_parent';
    }

    protected function syncLifecycleUserStatuses(ParentModel $parent, Student $student, bool $syncParentUserStatus): void
    {
        $parent->loadMissing('user');
        $student->loadMissing('user');

        if ($syncParentUserStatus && $parent->user) {
            $parent->user->status = $this->familyUserStatus($parent);
            $parent->user->save();
        }

        if ($student->user) {
            $student->user->status = $this->childUserStatus($student);
            $student->user->save();
        }
    }

    protected function recordChildTransferredIntoFamily(
        ParentModel $parent,
        Student $student,
        BookingChild $child,
        Booking $booking,
        string $familyStatus
    ): void {
        app(AccountHistoryService::class)->record($parent->id, AccountHistoryEventType::ChildTransferredIntoFamily->value, [
            'subject_type' => 'child',
            'subject_id' => $student->id,
            'new_value' => $student->account_status,
            'metadata' => [
                'source_booking_id' => $booking->id,
                'source_booking_child_id' => $child->id,
                'family_lifecycle_status' => $familyStatus,
            ],
        ]);
    }

    protected function familyUserStatus(ParentModel $parent): string
    {
        return FamilyLifecycleStatus::tryFrom((string) $parent->lifecycle_status)?->toUserStatus()
            ?? FamilyLifecycleStatus::PendingActivation->toUserStatus();
    }

    protected function childUserStatus(Student $student): string
    {
        return ChildAccountStatus::tryFrom((string) $student->account_status)?->toUserStatus()
            ?? ChildAccountStatus::PendingActivation->toUserStatus();
    }

    protected function generateChildUsername(Student $student): string
    {
        $prefix = Str::upper(Str::substr((string) ($student->first_name ?: 'ST'), 0, 2));
        $prefix = Str::padRight(preg_replace('/[^A-Z]/', '', $prefix) ?: 'ST', 2, 'X');

        for ($suffix = 101; $suffix <= 999; $suffix++) {
            $candidate = $prefix.$suffix;

            if (! User::where('name', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $prefix.($student->id + 1000);
    }

    /** @return list<string> */
    private function familyLifecycleStatuses(): array
    {
        return array_map(
            static fn (FamilyLifecycleStatus $status): string => $status->value,
            FamilyLifecycleStatus::cases()
        );
    }

    protected function assertExistingParentHasValidLifecycleStatus(ParentModel $parent): void
    {
        if (in_array($parent->lifecycle_status, $this->familyLifecycleStatuses(), true)) {
            return;
        }

        throw new InvalidArgumentException(
            'Existing family must be classified before transfer.'
        );
    }

    /** @return list<string> */
    private function childLifecycleStatuses(): array
    {
        return array_map(
            static fn (ChildAccountStatus $status): string => $status->value,
            ChildAccountStatus::cases()
        );
    }
}
