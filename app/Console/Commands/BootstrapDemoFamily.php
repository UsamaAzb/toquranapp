<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\AttachmentFile;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ClassSession;
use App\Models\GeneralLibraryFolder;
use App\Models\GeneralLibraryResource;
use App\Models\ParentModel;
use App\Models\RewardDisciplinePoint;
use App\Models\RewardPointsLedger;
use App\Models\RewardTotal;
use App\Models\Services_type;
use App\Models\SessionMaterial;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentGiftPointsHistory;
use App\Models\Student_Session_Discipline;
use App\Models\StudentsSubject;
use App\Models\TaskType;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Services\BookingTransferService;
use App\Services\Library\LibraryResourceAttachmentWriter;
use App\Support\BookingServiceInterest;
use App\Support\BookingSubjectProvisioning;
use App\Support\MyDeenJourneyLaunchDefaults;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BootstrapDemoFamily extends Command
{
    private const EXPECTED_DATABASE = 'u504065335_to_quran';

    private const FAMILY_REFERENCE = 'TQDEMO-001';

    private const LIBRARY_FOLDER_TITLE = "Tajweed Beginner's Book";

    private const EXPECTED_BEGINNER_BOOK_FILE_COUNT = 84;

    private const EXPECTED_BEGINNER_BOOK_BYTES = 182688675;

    protected $signature = 'toquran:bootstrap-demo-family
        {--confirm-db= : Required for writes; must match the active database name}
        {--dry-run : Preview without writing rows}
        {--skip-library-size-check : Allow resource count verification without exact total byte match}';

    protected $description = 'Create or verify the intentional TQ9 launch demo family and history.';

    /** @var array<string, int> */
    private array $subjects = [];

    /** @var array<string, mixed> */
    private array $context = [];

    public function handle(BookingTransferService $transferService, LibraryResourceAttachmentWriter $attachmentWriter): int
    {
        $dryRun = (bool) $this->option('dry-run');

        try {
            $this->guardDatabase($dryRun);
            $this->context = $this->resolveContext();
            $libraryResources = $this->verifyBeginnerBookResources();
            $quranRepetitionResources = $this->verifyQuranRepetitionResources();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('Demo family dry-run passed. No data was written.');
            $this->table(['Check', 'Value'], [
                ['DB', (string) DB::connection()->getDatabaseName()],
                ['Teacher', $this->context['teacher']->email],
                ['Library resources', (string) $libraryResources->count()],
                ['Quran repetition resources', (string) $quranRepetitionResources->count()],
                ['Family reference', self::FAMILY_REFERENCE],
            ]);

            return self::SUCCESS;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            $result = DB::transaction(function () use ($transferService, $attachmentWriter, $libraryResources, $quranRepetitionResources): array {
                $booking = $this->createOrUpdateBooking();
                $students = $this->transferDemoChildren($booking, $transferService);

                $summary = [
                    'booking_id' => $booking->id,
                    'students' => [],
                ];

                foreach ($students as $key => $student) {
                    $this->seedDemoGifts($student, $key, $this->giftNamesFor($key));
                    $this->seedDemoAcademicHistory($student, $key, $attachmentWriter, $libraryResources, $quranRepetitionResources);
                    $this->seedDemoDailyHistory($student, $key);
                    $this->seedDemoBehaviorHistory($student, $key);
                    $this->reconcileRewards($student, $key);

                    $summary['students'][$key] = $student->id;
                }

                return $summary;
            }, 3);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Demo family created or verified.');
        $this->table(['Record', 'ID'], collect($result['students'])
            ->map(fn (int $id, string $key): array => [$key, $id])
            ->prepend(['booking', $result['booking_id']])
            ->all());
        $this->warn('Production activation/email smoke remains a separate owner-approved step through the normal family lifecycle flow.');

        return self::SUCCESS;
    }

    private function guardDatabase(bool $dryRun): void
    {
        $database = DB::connection()->getDatabaseName();
        $confirmDb = trim((string) $this->option('confirm-db'));

        if (! is_string($database) || trim($database) === '') {
            throw new RuntimeException('Could not determine the active database name. Aborting.');
        }

        $database = trim($database);

        if (! $dryRun && $confirmDb !== $database) {
            throw new RuntimeException("Refusing to write demo family data. Pass --confirm-db={$database} after backup/target checks.");
        }

        if (! app()->environment('testing') && $database !== self::EXPECTED_DATABASE) {
            throw new RuntimeException("Refusing to run outside ".self::EXPECTED_DATABASE.". Current DB: {$database}");
        }

        $requiredTables = [
            'bookings',
            'booking_children',
            'parents',
            'students',
            'users',
            'class_sessions',
            'session_materials',
            'session_tasks',
            'session_task_student',
            'student_gifts',
            'reward_points_ledger',
            'reward_totals',
            'student_gift_points_history',
            'general_library_folders',
            'general_library_resources',
        ];

        $missing = collect($requiredTables)
            ->reject(fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all();

        if ($missing !== []) {
            throw new RuntimeException('Missing required tables: '.implode(', ', $missing));
        }

        if (! app()->environment('testing')) {
            $tableCount = (int) (DB::selectOne(
                'SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = DATABASE()'
            )->table_count ?? 0);

            if ($tableCount < 357) {
                throw new RuntimeException("Refusing demo write. Current DB has only {$tableCount} tables; expected launch baseline is at least 357.");
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveContext(): array
    {
        $teacher = User::query()
            ->where('email', config('toquran.default_teacher_email', 'drosamaqandil@gmail.com'))
            ->where(function ($query): void {
                $query->where('status', 'active')->orWhereNull('status');
            })
            ->first();

        if (! $teacher || ! $teacher->hasRole('teacher')) {
            throw new RuntimeException('Default teacher drosamaqandil@gmail.com is missing, inactive, or not assigned the teacher role.');
        }

        $superadminExists = User::role('super_admin')
            ->where(function ($query): void {
                $query->where('status', 'active')->orWhereNull('status');
            })
            ->exists();

        if (! $superadminExists) {
            throw new RuntimeException('No active superadmin account exists.');
        }

        $this->subjects = [
            'quran' => $this->requireSubject('Quran Memorization', BookingSubjectProvisioning::SUBJECT_QURAN_MEMORIZATION),
            'quranic_arabic' => $this->requireSubject('Quranic Arabic', BookingSubjectProvisioning::SUBJECT_QURANIC_ARABIC),
            'arabic_language' => $this->requireSubject('Arabic Language', BookingSubjectProvisioning::SUBJECT_ARABIC_LANGUAGE),
            'deen' => $this->requireSubject('My Deen Journey', BookingSubjectProvisioning::SUBJECT_MY_DEEN_JOURNEY),
            'wellbeing' => $this->requireSubject('Well Being', BookingSubjectProvisioning::SUBJECT_WELL_BEING),
        ];

        foreach (BookingServiceInterest::childFacingValues() as $serviceValue) {
            if (! Services_type::query()->where('value', $serviceValue)->exists()) {
                throw new RuntimeException("Missing services_types.value '{$serviceValue}'.");
            }
        }

        $assignmentTaskType = TaskType::query()
            ->where(function ($query): void {
                $query->whereKey(7)->orWhereRaw('LOWER(title) = ?', ['assignment']);
            })
            ->orderByRaw('CASE WHEN id = 7 THEN 0 ELSE 1 END')
            ->first();

        if (! $assignmentTaskType) {
            throw new RuntimeException('Missing Assignment task type.');
        }

        return [
            'academic_year_id' => AcademicYear::currentId(),
            'teacher' => $teacher,
            'task_type_id' => (int) $assignmentTaskType->id,
        ];
    }

    private function requireSubject(string $title, int $expectedId): int
    {
        $row = DB::table('subjects')
            ->where('id', $expectedId)
            ->whereRaw('LOWER(title) = ?', [strtolower($title)])
            ->first();

        if (! $row) {
            throw new RuntimeException("Missing expected subject {$expectedId}: {$title}.");
        }

        $gradeLevelSubjectExists = DB::table('grade_level_subjects')
            ->where('grade_level_id', 2)
            ->where('subject_id', $expectedId)
            ->where('status', 'active')
            ->exists();

        if (! $gradeLevelSubjectExists) {
            throw new RuntimeException("Missing active Beginner grade_level_subject for {$title}.");
        }

        return $expectedId;
    }

    /**
     * @return Collection<int, GeneralLibraryResource>
     */
    private function verifyBeginnerBookResources(): Collection
    {
        $folder = GeneralLibraryFolder::query()
            ->where('title', self::LIBRARY_FOLDER_TITLE)
            ->where('status', GeneralLibraryFolder::STATUS_ACTIVE)
            ->first();

        if (! $folder) {
            throw new RuntimeException("Missing active General Library folder: ".self::LIBRARY_FOLDER_TITLE);
        }

        $resources = GeneralLibraryResource::query()
            ->where('general_library_folder_id', $folder->id)
            ->where('status', GeneralLibraryResource::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($resources->count() !== self::EXPECTED_BEGINNER_BOOK_FILE_COUNT) {
            throw new RuntimeException(sprintf(
                "The %s folder has %d active resources; expected %d.",
                self::LIBRARY_FOLDER_TITLE,
                $resources->count(),
                self::EXPECTED_BEGINNER_BOOK_FILE_COUNT
            ));
        }

        $registeredBytes = (int) $resources->sum(fn (GeneralLibraryResource $resource): int => (int) ($resource->file_size ?? 0));
        if (! (bool) $this->option('skip-library-size-check') && $registeredBytes > 0 && $registeredBytes !== self::EXPECTED_BEGINNER_BOOK_BYTES) {
            throw new RuntimeException(sprintf(
                "The %s folder reports %d bytes; expected %d. Use --skip-library-size-check only after manual verification.",
                self::LIBRARY_FOLDER_TITLE,
                $registeredBytes,
                self::EXPECTED_BEGINNER_BOOK_BYTES
            ));
        }

        $missingStorage = $resources
            ->filter(fn (GeneralLibraryResource $resource): bool => $resource->resource_type === GeneralLibraryResource::TYPE_FILE
                && blank($resource->storage_disk))
            ->count();

        if ($missingStorage > 0) {
            throw new RuntimeException("The {$missingStorage} beginner-book resources are missing storage_disk values.");
        }

        $missingFiles = $resources
            ->filter(fn (GeneralLibraryResource $resource): bool => $resource->resource_type === GeneralLibraryResource::TYPE_FILE)
            ->reject(function (GeneralLibraryResource $resource): bool {
                $disk = (string) ($resource->storage_disk ?: 'local');
                $path = ltrim((string) $resource->file_path, '/');

                try {
                    return $path !== '' && Storage::disk($disk)->exists($path);
                } catch (\InvalidArgumentException) {
                    return false;
                }
            })
            ->pluck('title')
            ->take(5)
            ->values()
            ->all();

        if ($missingFiles !== []) {
            throw new RuntimeException('Beginner-book resources are missing stored files: '.implode(', ', $missingFiles));
        }

        return $resources;
    }

    /**
     * @return Collection<int, GeneralLibraryResource>
     */
    private function verifyQuranRepetitionResources(): Collection
    {
        $root = GeneralLibraryFolder::query()
            ->whereNull('parent_id')
            ->where('title', 'Quran Repetition')
            ->where('status', GeneralLibraryFolder::STATUS_ACTIVE)
            ->first();

        if (! $root) {
            throw new RuntimeException('Missing active General Library folder: Quran Repetition');
        }

        $surahFolders = GeneralLibraryFolder::query()
            ->where('parent_id', $root->id)
            ->whereIn('title', [
                '001. Al-Faatiha',
                '112. Al-Ikhlaas',
                '113. Al-Falaq',
                '114. An-Naas',
            ])
            ->where('status', GeneralLibraryFolder::STATUS_ACTIVE)
            ->get()
            ->keyBy('title');

        $missingFolders = collect([
            '001. Al-Faatiha',
            '112. Al-Ikhlaas',
            '113. Al-Falaq',
            '114. An-Naas',
        ])->reject(fn (string $title): bool => $surahFolders->has($title))->values()->all();

        if ($missingFolders !== []) {
            throw new RuntimeException('Missing Quran Repetition surah folders: '.implode(', ', $missingFolders));
        }

        $resources = GeneralLibraryResource::query()
            ->with('folder:id,title,parent_id')
            ->whereIn('general_library_folder_id', $surahFolders->pluck('id')->all())
            ->where('status', GeneralLibraryResource::STATUS_ACTIVE)
            ->where('resource_type', GeneralLibraryResource::TYPE_YOUTUBE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($this->requiredQuranRepetitionSelections() as $selection) {
            if (! $this->quranRepetitionResource($resources, $selection)) {
                throw new RuntimeException(sprintf(
                    'Missing Quran Repetition resource: %s / %s.',
                    $selection['folder'],
                    $selection['resource']
                ));
            }
        }

        return $resources;
    }

    private function createOrUpdateBooking(): Booking
    {
        $booking = Booking::query()->firstOrNew(['booking_reference' => self::FAMILY_REFERENCE]);

        $booking->forceFill([
            'parent_name' => 'Demo Parent Amina',
            'parent_email' => 'osama.salem0217@gmail.com',
            'parent_phone' => '+201091051913',
            'child_name' => 'Yusuf Demo',
            'child_age' => 5,
            'child_grade' => 2,
            'current_school' => 'Demo homeschool',
            'school_system' => 'Other',
            'service_interest' => implode(', ', [
                BookingServiceInterest::QURAN_MEMORIZATION,
                BookingServiceInterest::QURANIC_ARABIC,
                BookingServiceInterest::MY_DEEN_JOURNEY,
            ]),
            'contact_method' => 'email',
            'consultation_type' => 'online',
            'status' => 'fit',
            'terms' => 1,
            'notes' => json_encode([
                'reference' => self::FAMILY_REFERENCE,
                'source' => 'demo_family_command',
                'country' => 'Egypt',
            ], JSON_UNESCAPED_SLASHES),
        ])->save();

        return $booking->fresh();
    }

    /**
     * @return array<string, Student>
     */
    private function transferDemoChildren(Booking $booking, BookingTransferService $transferService): array
    {
        $children = [
            'yusuf' => [
                'name' => 'Yusuf Demo',
                'age' => 5,
                'services' => [
                    BookingServiceInterest::QURAN_MEMORIZATION,
                    BookingServiceInterest::QURANIC_ARABIC,
                    BookingServiceInterest::MY_DEEN_JOURNEY,
                ],
            ],
            'maryam' => [
                'name' => 'Maryam Demo',
                'age' => 6,
                'services' => [
                    BookingServiceInterest::QURAN_MEMORIZATION,
                    BookingServiceInterest::QURANIC_ARABIC,
                    BookingServiceInterest::MY_DEEN_JOURNEY,
                ],
            ],
            'omar' => [
                'name' => 'Omar Demo',
                'age' => 9,
                'services' => [
                    BookingServiceInterest::QURAN_MEMORIZATION,
                    BookingServiceInterest::ARABIC_LANGUAGE,
                    BookingServiceInterest::MY_DEEN_JOURNEY,
                ],
            ],
        ];

        $students = [];
        $sort = 1;

        foreach ($children as $key => $child) {
            $bookingChild = BookingChild::query()
                ->where('booking_id', $booking->id)
                ->where('child_name', $child['name'])
                ->firstOrNew();

            $bookingChild->forceFill([
                'booking_id' => $booking->id,
                'child_name' => $child['name'],
                'child_age' => $child['age'],
                'child_grade' => 2,
                'school_system' => 'Other',
                'current_school' => 'Demo homeschool',
                'service_interests' => $child['services'],
                'consultation_type' => 'undecided',
                'consultation_status' => 'confirmed',
                'evaluation_outcome' => 'fit',
                'meeting_disposition' => 'no_meeting_required',
                'transfer_status' => $bookingChild->student_id ? 'transferred' : 'not_transferred',
                'sort_order' => $sort++,
                'notes' => json_encode([
                    'reference' => self::FAMILY_REFERENCE,
                    'child_key' => $key,
                    'source' => 'demo_family_command',
                ], JSON_UNESCAPED_SLASHES),
            ])->save();

            if (! $bookingChild->student_id) {
                $transferService->transferChild($bookingChild->fresh());
            }

            $student = Student::query()->find((int) $bookingChild->fresh()->student_id);
            if (! $student) {
                throw new RuntimeException("Transfer did not produce a student for {$child['name']}.");
            }

            $student->forceFill([
                'first_name' => explode(' ', $child['name'], 2)[0],
                'last_name' => 'Demo',
                'age' => $child['age'],
            ])->save();

            $student = $student->fresh();
            $this->enforceDemoSubjectSet($student, $key);

            $students[$key] = $student->fresh();
        }

        return $students;
    }

    private function enforceDemoSubjectSet(Student $student, string $childKey): void
    {
        $activeSubjectIds = match ($childKey) {
            'omar' => [
                $this->subjects['quran'],
                $this->subjects['arabic_language'],
                $this->subjects['deen'],
                $this->subjects['wellbeing'],
            ],
            default => [
                $this->subjects['quran'],
                $this->subjects['quranic_arabic'],
                $this->subjects['deen'],
                $this->subjects['wellbeing'],
            ],
        };

        $studentSubjects = StudentsSubject::query()
            ->where('student_id', $student->id)
            ->with('gradeLevelSubject')
            ->get();

        foreach ($studentSubjects as $studentSubject) {
            $subjectId = (int) $studentSubject->gradeLevelSubject?->subject_id;
            if ($subjectId <= 0) {
                continue;
            }

            $targetStatus = in_array($subjectId, $activeSubjectIds, true) ? 'active' : 'inactive';
            if ($studentSubject->status !== $targetStatus) {
                $studentSubject->status = $targetStatus;
                $studentSubject->save();
            }

            BookingSubjectProvisioning::syncTeacherSubjectClassStatus((int) $studentSubject->class_subject_id);
        }
    }

    /**
     * @param list<string> $names
     */
    private function seedDemoGifts(Student $student, string $childKey, array $names): void
    {
        $academicYearId = (int) $this->context['academic_year_id'];
        $reachedDates = [
            '2024-06-20', '2024-07-08', '2024-08-03', '2024-09-01', '2024-10-14',
            '2024-11-05', '2024-12-10', '2025-01-10', '2025-02-12', '2025-03-18',
        ];
        $redeemedDates = [
            '2024-06-25', '2024-07-13', '2024-08-21', '2024-09-06', '2024-10-29',
            '2024-11-12', '2024-12-28', '2025-02-02', '2025-03-05', '2025-04-01',
        ];

        foreach ($names as $index => $name) {
            $giftNumber = $index + 1;
            $pointsRequired = $giftNumber <= 10
                ? $giftNumber * 100
                : 1000 + (($giftNumber - 10) * 250);

            $attributes = [
                'gift_name' => $name,
                'gift_image' => $this->demoGiftImagePath($childKey, $name),
                'gift_id' => null,
                'points_required' => $pointsRequired,
                'status' => match (true) {
                    $giftNumber <= 10 => StudentGift::STATUS_REDEEMED,
                    $giftNumber === 11 => StudentGift::STATUS_PENDING,
                    default => StudentGift::STATUS_WAITING,
                },
                'approved_by_id' => null,
                'approved_by_name' => null,
                'created_at' => CarbonImmutable::parse('2024-06-01')->addDays($giftNumber)->toDateTimeString(),
                'reached_at' => $giftNumber <= 10 ? $reachedDates[$index] : null,
                'redeemed_at' => $giftNumber <= 10 ? $redeemedDates[$index] : null,
                'gift_order' => $giftNumber,
            ];

            StudentGift::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'points_required' => $pointsRequired,
                ],
                $attributes
            );
        }
    }

    private function demoGiftImagePath(string $childKey, string $giftName): ?string
    {
        $folder = match ($childKey) {
            'yusuf' => 'Yusuf',
            'maryam' => 'Maryam',
            default => 'Omar',
        };

        $baseName = preg_replace('/[^A-Za-z0-9]+/', '-', $giftName);
        $baseName = trim((string) $baseName, '-');

        foreach (['webp', 'jpg', 'jpeg', 'png', 'gif'] as $extension) {
            $source = base_path("resources/demo-gifts/{$folder}/{$baseName}.{$extension}");

            if (! is_file($source)) {
                continue;
            }

            $target = "gifts/demo-family/{$folder}/{$baseName}.{$extension}";
            Storage::disk('public')->put($target, file_get_contents($source));

            return $target;
        }

        return null;
    }

    /**
     * @param Collection<int, GeneralLibraryResource> $libraryResources
     */
    private function seedDemoAcademicHistory(
        Student $student,
        string $childKey,
        LibraryResourceAttachmentWriter $attachmentWriter,
        Collection $libraryResources,
        Collection $quranRepetitionResources
    ): void {
        $plans = $this->academicPlans($childKey);

        foreach ($plans as $subjectKey => $tasks) {
            foreach ($tasks as $index => $taskSet) {
                $date = CarbonImmutable::parse('2026-03-30')->addDays($index * 3);
                $this->createDemoSessionWithTasks(
                    student: $student,
                    subjectId: $this->subjects[$subjectKey],
                    title: $taskSet['session_title'],
                    date: $date,
                    tasks: $taskSet['tasks'],
                    status: $taskSet['status'],
                    attachmentWriter: $attachmentWriter,
                    libraryResources: $libraryResources,
                    quranRepetitionResources: $quranRepetitionResources,
                    key: "{$childKey}:{$subjectKey}:{$index}"
                );
            }
        }
    }

    /**
     * @return array<string, list<array{session_title:string,status:string,tasks:list<array{title:string,description:string,points:int,library?:string,quran_repetition?:array{folder:string,resource:string}}>}>> 
     */
    private function academicPlans(string $childKey): array
    {
        $quran = match ($childKey) {
            'omar' => [
                ['Al-Faatiha ayahs 1-3', 'Memorize and practice Ayahs 1-3 from Surah Al-Faatiha'],
                ['Al-Faatiha ayahs 4-6', 'Memorize and practice Ayahs 4-6 from Surah Al-Faatiha'],
                ['Al-Faatiha ayah 7', 'Memorize and practice Ayah 7 from Surah Al-Faatiha'],
                ['An-Naas ayahs 1-3', 'Memorize and practice Ayahs 1-3 from Surah An-Naas'],
                ['An-Naas ayahs 4-6', 'Memorize and practice Ayahs 4-6 from Surah An-Naas'],
                ['Al-Falaq ayahs 1-3', 'Memorize and practice Ayahs 1-3 from Surah Al-Falaq'],
                ['Al-Falaq ayahs 4-5', 'Memorize and practice Ayahs 4-5 from Surah Al-Falaq'],
                ['Al-Ikhlaas practice', 'Practice the full Surah Al-Ikhlaas'],
            ],
            default => [
                ['Al-Faatiha ayah 1', 'Memorize and practice Ayah 1 from Surah Al-Faatiha'],
                ['Al-Faatiha ayah 2', 'Memorize and practice Ayah 2 from Surah Al-Faatiha'],
                ['Al-Faatiha ayah 3', 'Memorize and practice Ayah 3 from Surah Al-Faatiha'],
                ['Al-Faatiha ayah 4', 'Memorize and practice Ayah 4 from Surah Al-Faatiha'],
                ['Al-Faatiha ayah 5', 'Memorize and practice Ayah 5 from Surah Al-Faatiha'],
                ['Al-Faatiha ayah 6', 'Memorize and practice Ayah 6 from Surah Al-Faatiha'],
                ['Al-Faatiha ayah 7', 'Memorize and practice Ayah 7 from Surah Al-Faatiha'],
                ['An-Naas ayah 1', 'Memorize and practice Ayah 1 from Surah An-Naas'],
                ['An-Naas ayah 2', 'Memorize and practice Ayah 2 from Surah An-Naas'],
                ['An-Naas ayah 3', 'Memorize and practice Ayah 3 from Surah An-Naas'],
                ['An-Naas ayah 4', 'Memorize and practice Ayah 4 from Surah An-Naas'],
                ['An-Naas ayah 5', 'Memorize and practice Ayah 5 from Surah An-Naas'],
                ['An-Naas ayah 6', 'Memorize and practice Ayah 6 from Surah An-Naas'],
                ['Al-Falaq ayah 1', 'Memorize and practice Ayah 1 from Surah Al-Falaq'],
            ],
        };

        $quranRows = collect($quran)->map(function (array $row, int $index): array {
            $selection = $this->quranRepetitionSelectorForLabel($row[0]);

            return [
                'session_title' => 'Quran Memorization - '.$row[0],
                'status' => $this->taskStatusForIndex($index),
                'tasks' => [
                    ['title' => $row[0], 'description' => $row[1], 'points' => 8],
                    array_filter(
                        ['title' => 'Watch the repetition video', 'description' => 'Use the matching Quran repetition video before class.', 'points' => 5, 'quran_repetition' => $selection],
                        fn ($value): bool => $value !== null
                    ),
                ],
            ];
        })->all();

        $plans = ['quran' => $quranRows];

        if (in_array($childKey, ['yusuf', 'maryam'], true)) {
            $arabicTitles = $childKey === 'yusuf'
                ? ['Arabic Alphabet', 'Letter Recognition', 'Letter Position 1', 'Letter Position 2', 'Connecting Letters 1', 'Fathah']
                : ['Arabic Alphabet', 'Letter Recognition', 'Letter Position 1', 'Letter Position 2', 'Letter Position 3', 'Connecting Letters 1', 'Connecting Letters 2', 'Fathah', 'Words with Fathah', 'Kasrah'];

            $plans['quranic_arabic'] = collect($arabicTitles)->map(fn (string $title, int $index): array => [
                'session_title' => 'Quranic Arabic - '.$title,
                'status' => $this->taskStatusForIndex($index),
                'tasks' => [
                    ['title' => $title.' practice', 'description' => 'Read, point, and say the page slowly with the teacher.', 'points' => 7, 'library' => $title],
                    ['title' => $title.' page/video review', 'description' => 'Open the attached page and video when available.', 'points' => 5, 'library' => $title],
                ],
            ])->all();
        }

        if ($childKey === 'omar') {
            $plans['arabic_language'] = collect([
                'Short vowels reading',
                'Dictation practice',
                'Sentence building',
                'Reading fluency',
                'Copywork with neat handwriting',
                'Review weak letters',
            ])->map(fn (string $title, int $index): array => [
                'session_title' => 'Arabic Language - '.$title,
                'status' => $this->taskStatusForIndex($index),
                'tasks' => [
                    ['title' => $title, 'description' => 'Complete the Arabic reading and writing practice.', 'points' => 8],
                ],
            ])->all();
        }

        return $plans;
    }

    private function taskStatusForIndex(int $index): string
    {
        if ($index % 9 === 8) {
            return SessionTaskStudent::STATUS_IN_REVIEW;
        }

        if ($index >= 12) {
            return SessionTaskStudent::STATUS_ASSIGNED;
        }

        return SessionTaskStudent::STATUS_COMPLETED;
    }

    /**
     * @param list<array{title:string,description:string,points:int,library?:string,quran_repetition?:array{folder:string,resource:string}}> $tasks
     * @param Collection<int, GeneralLibraryResource> $libraryResources
     * @param Collection<int, GeneralLibraryResource> $quranRepetitionResources
     */
    private function createDemoSessionWithTasks(
        Student $student,
        int $subjectId,
        string $title,
        CarbonImmutable $date,
        array $tasks,
        string $status,
        LibraryResourceAttachmentWriter $attachmentWriter,
        Collection $libraryResources,
        Collection $quranRepetitionResources,
        string $key
    ): void {
        $studentSubject = $this->activeStudentSubject($student, $subjectId);
        $teacherSubjectClass = $this->teacherSubjectClassFor($studentSubject, $subjectId);
        $teacher = $this->context['teacher'];

        $session = ClassSession::query()->updateOrCreate(
            [
                'teacher_subject_classes_id' => $teacherSubjectClass->id,
                'class_id' => $student->current_class_id,
                'subject_id' => $subjectId,
                'date' => $date->toDateString(),
                'title' => '[Demo] '.$title,
            ],
            [
                'grade_id' => $student->grade_level_id ?: 2,
                'teacher_id' => $teacher->id,
                'unit_id' => 0,
                'session_start_time' => '18:00:00',
                'session_end_time' => '18:30:00',
                'class_subject_id' => $studentSubject->class_subject_id,
            ]
        );

        $material = SessionMaterial::query()->updateOrCreate(
            ['session_id' => $session->id],
            [
                'teacher_subject_classes_id' => $teacherSubjectClass->id,
                'subject_id' => $subjectId,
                'grade_id' => $student->grade_level_id ?: 2,
                'teacher_id' => $teacher->id,
                'unit_id' => 0,
                'status' => 'published',
                'assign_to_all' => 'custom',
                'task_desc' => self::FAMILY_REFERENCE.' '.$key,
                'class_work_desc' => null,
            ]
        );

        foreach ($tasks as $sort => $taskData) {
            $task = SessionTask::query()->updateOrCreate(
                [
                    'class_session_id' => $session->id,
                    'title' => $taskData['title'],
                    'created_by_teacher_id' => $teacher->id,
                ],
                [
                    'description' => $taskData['description'].' ('.self::FAMILY_REFERENCE.')',
                    'task_type_id' => (int) $this->context['task_type_id'],
                    'due_date' => $date->addDays(2)->toDateTimeString(),
                    'assign_to_all' => 'custom',
                    'default_points' => $taskData['points'],
                    'max_points' => max(10, $taskData['points']),
                    'session_material_id' => $material->id,
                    'status' => 'published',
                    'created_at' => $date->toDateTimeString(),
                    'sort' => $sort + 1,
                ]
            );

            $pivotAttributes = [
                'student_points' => $status === SessionTaskStudent::STATUS_COMPLETED ? $taskData['points'] : null,
                'submitted_at' => $date->addDay()->toDateTimeString(),
                'review_submitted_at' => $status === SessionTaskStudent::STATUS_IN_REVIEW ? $date->addDay()->toDateTimeString() : null,
                'review_submission_source' => $status === SessionTaskStudent::STATUS_IN_REVIEW ? SessionTaskStudent::SOURCE_STUDENT_REVIEW : null,
                'approval_source' => $status === SessionTaskStudent::STATUS_COMPLETED ? SessionTaskStudent::SOURCE_TEACHER_APPROVAL : null,
                'approved_by_id' => $status === SessionTaskStudent::STATUS_COMPLETED ? $teacher->id : null,
                'approved_at' => $status === SessionTaskStudent::STATUS_COMPLETED ? $date->addDays(2)->toDateTimeString() : null,
                'assign_to_all' => 'custom',
                'status' => $status,
                'flag' => $status === SessionTaskStudent::STATUS_ASSIGNED && $sort === 0 ? 'up-next' : null,
            ];

            SessionTaskStudent::query()->updateOrCreate(
                ['session_task_id' => $task->id, 'student_id' => $student->id],
                $pivotAttributes
            );

            if (isset($taskData['library'])) {
                $resourceIds = $this->matchingBeginnerBookSelections($libraryResources, $taskData['library']);

                foreach ($resourceIds as $resourceId) {
                    $resource = $libraryResources->first(fn (GeneralLibraryResource $candidate): bool => (int) $candidate->id === $resourceId);

                    if ($resource instanceof GeneralLibraryResource) {
                        $this->attachGeneralLibraryResource($attachmentWriter, $task, $session, $resource, (int) $teacher->id);
                    }
                }
            }

            if (isset($taskData['quran_repetition'])) {
                $resource = $this->quranRepetitionResource($quranRepetitionResources, $taskData['quran_repetition']);

                if (! $resource instanceof GeneralLibraryResource) {
                    throw new RuntimeException(sprintf(
                        'Could not attach Quran Repetition resource for %s / %s.',
                        $taskData['quran_repetition']['folder'],
                        $taskData['quran_repetition']['resource']
                    ));
                }

                $this->attachGeneralLibraryResource($attachmentWriter, $task, $session, $resource, (int) $teacher->id);
            }
        }
    }

    private function attachGeneralLibraryResource(
        LibraryResourceAttachmentWriter $attachmentWriter,
        SessionTask $task,
        ClassSession $session,
        GeneralLibraryResource $resource,
        int $teacherId
    ): void {
        if (AttachmentFile::query()
            ->where('session_task_id', $task->id)
            ->where('title', $resource->title)
            ->exists()) {
            return;
        }

        $attachmentWriter->writeOneForTaskAtSortOrder(
            $task,
            $session,
            'general__'.$resource->id,
            $teacherId,
            ((int) AttachmentFile::query()->where('session_task_id', $task->id)->max('sort_order')) + 1
        );
    }

    /**
     * @return list<array{folder:string,resource:string}>
     */
    private function requiredQuranRepetitionSelections(): array
    {
        return [
            ['folder' => '001. Al-Faatiha', 'resource' => 'Ayahs 1-3'],
            ['folder' => '001. Al-Faatiha', 'resource' => 'Ayahs 4-6'],
            ['folder' => '001. Al-Faatiha', 'resource' => 'Ayah 7'],
            ['folder' => '114. An-Naas', 'resource' => 'Ayahs 1-3'],
            ['folder' => '114. An-Naas', 'resource' => 'Ayahs 4-6'],
            ['folder' => '113. Al-Falaq', 'resource' => 'Ayahs 1-3'],
            ['folder' => '113. Al-Falaq', 'resource' => 'Ayahs 4-5'],
            ['folder' => '112. Al-Ikhlaas', 'resource' => 'Full Surah'],
        ];
    }

    /**
     * @return array{folder:string,resource:string}|null
     */
    private function quranRepetitionSelectorForLabel(string $label): ?array
    {
        $normalized = strtolower($label);

        if (str_contains($normalized, 'al-faatiha')) {
            if (str_contains($normalized, 'ayah 7')) {
                return ['folder' => '001. Al-Faatiha', 'resource' => 'Ayah 7'];
            }

            if (str_contains($normalized, '4') || str_contains($normalized, '5') || str_contains($normalized, '6')) {
                return ['folder' => '001. Al-Faatiha', 'resource' => 'Ayahs 4-6'];
            }

            return ['folder' => '001. Al-Faatiha', 'resource' => 'Ayahs 1-3'];
        }

        if (str_contains($normalized, 'an-naas')) {
            if (str_contains($normalized, '4') || str_contains($normalized, '5') || str_contains($normalized, '6')) {
                return ['folder' => '114. An-Naas', 'resource' => 'Ayahs 4-6'];
            }

            return ['folder' => '114. An-Naas', 'resource' => 'Ayahs 1-3'];
        }

        if (str_contains($normalized, 'al-falaq')) {
            if (str_contains($normalized, '4') || str_contains($normalized, '5')) {
                return ['folder' => '113. Al-Falaq', 'resource' => 'Ayahs 4-5'];
            }

            return ['folder' => '113. Al-Falaq', 'resource' => 'Ayahs 1-3'];
        }

        if (str_contains($normalized, 'al-ikhlaas')) {
            return ['folder' => '112. Al-Ikhlaas', 'resource' => 'Full Surah'];
        }

        return null;
    }

    /**
     * @param Collection<int, GeneralLibraryResource> $resources
     * @param array{folder:string,resource:string} $selection
     */
    private function quranRepetitionResource(Collection $resources, array $selection): ?GeneralLibraryResource
    {
        return $resources->first(function (GeneralLibraryResource $resource) use ($selection): bool {
            return $resource->folder?->title === $selection['folder']
                && strcasecmp((string) $resource->title, $selection['resource']) === 0;
        });
    }

    /**
     * @param Collection<int, GeneralLibraryResource> $resources
     * @return list<int>
     */
    private function matchingBeginnerBookSelections(Collection $resources, string $needle): array
    {
        $tokens = collect(preg_split('/\s+/', strtolower($needle)) ?: [])
            ->filter(fn (string $token): bool => strlen($token) > 2)
            ->values();

        return $resources
            ->filter(function (GeneralLibraryResource $resource) use ($tokens): bool {
                $title = strtolower((string) $resource->title.' '.(string) $resource->original_filename);

                return $tokens->contains(fn (string $token): bool => str_contains($title, $token));
            })
            ->take(2)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    private function seedDemoDailyHistory(Student $student, string $childKey): void
    {
        $dailyTasks = match ($childKey) {
            'yusuf' => ['Maghrib Prayer', 'Morning Dua', 'Evening Dua', 'Brush teeth', 'Shower day', 'Sleep readiness', 'Put learning materials away'],
            'maryam' => ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha', 'Morning Azkar 1-5', 'Evening Azkar 1-5', 'Brush teeth', 'Prepare learning place'],
            default => ['Fajr in mosque', 'Dhuhr', 'Asr', 'Maghrib in mosque', 'Isha in mosque', 'Morning Azkar', 'Evening Azkar', 'Device limit respected', 'Help younger sibling'],
        };

        foreach (range(0, 20) as $dayIndex) {
            $date = CarbonImmutable::parse('2026-06-01')->addDays($dayIndex);
            foreach ($dailyTasks as $taskIndex => $taskTitle) {
                $subjectId = str_contains(strtolower($taskTitle), 'brush')
                    || str_contains(strtolower($taskTitle), 'shower')
                    || str_contains(strtolower($taskTitle), 'sleep')
                    || str_contains(strtolower($taskTitle), 'device')
                    || str_contains(strtolower($taskTitle), 'materials')
                    ? $this->subjects['wellbeing']
                    : $this->subjects['deen'];

                $status = $dayIndex >= 19
                    ? SessionTaskStudent::STATUS_ASSIGNED
                    : ($dayIndex % 8 === 7 && $taskIndex === 0 ? SessionTaskStudent::STATUS_IN_REVIEW : SessionTaskStudent::STATUS_COMPLETED);

                $this->createDemoSessionWithTasks(
                    student: $student,
                    subjectId: $subjectId,
                    title: $taskTitle.' - '.$date->toDateString(),
                    date: $date,
                    tasks: [[
                        'title' => $taskTitle,
                        'description' => 'Daily demo routine item for '.$student->first_name.'.',
                        'points' => 3,
                    ]],
                    status: $status,
                    attachmentWriter: app(LibraryResourceAttachmentWriter::class),
                    libraryResources: collect(),
                    quranRepetitionResources: collect(),
                    key: "{$childKey}:daily:{$dayIndex}:{$taskIndex}"
                );
            }
        }
    }

    private function seedDemoBehaviorHistory(Student $student, string $childKey): void
    {
        app(MyDeenJourneyLaunchDefaults::class)->ensureBehaviorTemplates($student->id);

        $behaviors = match ($childKey) {
            'yusuf' => [
                ['Good Effort', 'Positive', 4, 'Tried again after a difficult ayah.'],
                ['Good Adab', 'Positive', 3, 'Started class with good adab.'],
                ['Low Practice', 'Slip', 1, 'Needed one reminder to slow down during recitation.'],
                ['Responsibility', 'Positive', 2, 'Cleaned toys before class.'],
            ],
            'maryam' => [
                ['Responsibility', 'Positive', 5, 'Marked all Salahs for the day.'],
                ['Helping Others', 'Positive', 3, 'Helped Yusuf prepare his page.'],
                ['Task Not Done', 'Slip', 1, 'Needed a parent reminder.'],
                ['Focused', 'Positive', 4, 'Read the page carefully.'],
            ],
            default => [
                ['Responsibility', 'Positive', 5, 'Kept mosque Salah habit.'],
                ['Helping Others', 'Positive', 4, 'Helped with page review.'],
                ['Device Slip', 'Slip', 2, 'Needed a device reminder.'],
                ['Self-Control', 'Positive', 4, 'Reviewed without rushing.'],
            ],
        };

        $teacherSubjectClass = $this->teacherSubjectClassFor(
            $this->activeStudentSubject($student, $this->subjects['deen']),
            $this->subjects['deen']
        );

        foreach ($behaviors as $index => [$title, $type, $points, $description]) {
            $createdAt = CarbonImmutable::parse('2026-06-05')->addDays($index * 4)->toDateTimeString();
            $behaviorTemplate = $this->rewardBehaviorTemplate($student, $title, $type);

            Student_Session_Discipline::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'created_at' => $createdAt,
                ],
                [
                    'title' => $title,
                    'discipline_icon_id' => (int) ($behaviorTemplate?->discipline_icon_id ?: 1),
                    'discipline_icon_path' => (string) ($behaviorTemplate?->discipline_icon_path ?: 'images/discipline/respect.png'),
                    'class_session_id' => null,
                    'teacher_subject_classes_id' => $teacherSubjectClass->id,
                    'student_reward_discipline_id' => $behaviorTemplate?->id,
                    'points' => $points,
                    'description' => $description,
                    'type' => $type,
                    'updated_at' => $createdAt,
                ]
            );
        }
    }

    private function rewardBehaviorTemplate(Student $student, string $title, string $type): ?RewardDisciplinePoint
    {
        if (! Schema::hasTable('reward_discipline_points')) {
            return null;
        }

        return RewardDisciplinePoint::query()
            ->where('title', $title)
            ->where('type', $type)
            ->where(function ($query) use ($student): void {
                $query->where('student_id', $student->id)
                    ->orWhereNull('student_id');
            })
            ->orderByRaw('CASE WHEN student_id = ? THEN 0 ELSE 1 END', [$student->id])
            ->first();
    }

    private function reconcileRewards(Student $student, string $childKey): void
    {
        $academicYearId = (int) $this->context['academic_year_id'];
        $teacher = $this->context['teacher'];

        RewardPointsLedger::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('source_type', ['task', 'discipline', 'adjustment'])
            ->where('comment', 'like', self::FAMILY_REFERENCE.'%')
            ->delete();

        RewardTotal::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->delete();

        $total = 0;
        $subjectTotals = [];

        $completedPivots = SessionTaskStudent::query()
            ->where('student_id', $student->id)
            ->where('status', SessionTaskStudent::STATUS_COMPLETED)
            ->whereNotNull('student_points')
            ->whereHas('task', fn ($query) => $query->where('description', 'like', '%'.self::FAMILY_REFERENCE.'%'))
            ->with('task.classSession')
            ->get();

        foreach ($completedPivots as $pivot) {
            $points = (int) $pivot->student_points;
            $subjectId = (int) $pivot->task?->classSession?->subject_id;

            if ($points <= 0 || $subjectId <= 0) {
                continue;
            }

            $total += $points;
            $subjectTotals[$subjectId] = ($subjectTotals[$subjectId] ?? 0) + $points;

            RewardPointsLedger::query()->create([
                'student_id' => $student->id,
                'source_type' => 'task',
                'source_id' => $pivot->session_task_id,
                'points_delta' => $points,
                'granted_by' => $teacher->id,
                'granted_at' => $pivot->approved_at ?: now(),
                'academic_year_id' => $academicYearId,
                'subject_id' => $subjectId,
                'comment' => self::FAMILY_REFERENCE.' task '.$childKey,
                'sign' => 'plus',
            ]);
        }

        $behaviorRows = Student_Session_Discipline::query()
            ->where('student_id', $student->id)
            ->whereIn('created_at', collect([0, 1, 2, 3])
                ->map(fn (int $index): string => CarbonImmutable::parse('2026-06-05')->addDays($index * 4)->toDateTimeString())
                ->all())
            ->get();

        foreach ($behaviorRows as $behavior) {
            $delta = in_array($behavior->type, ['Slip', 'No Way'], true)
                ? -1 * abs((int) $behavior->points)
                : abs((int) $behavior->points);
            $subjectId = $this->subjects['deen'];

            $total += $delta;
            $subjectTotals[$subjectId] = ($subjectTotals[$subjectId] ?? 0) + $delta;

            RewardPointsLedger::query()->create([
                'student_id' => $student->id,
                'source_type' => 'discipline',
                'source_id' => $behavior->id,
                'points_delta' => $delta,
                'granted_by' => $teacher->id,
                'granted_at' => $behavior->created_at ?: now(),
                'academic_year_id' => $academicYearId,
                'subject_id' => $subjectId,
                'comment' => self::FAMILY_REFERENCE.' behavior '.$childKey,
                'sign' => $delta < 0 ? 'minus' : 'plus',
            ]);
        }

        $targetTotal = match ($childKey) {
            'yusuf' => 1040,
            'maryam' => 1065,
            default => 1090,
        };

        $adjustment = $targetTotal - $total;
        if ($adjustment !== 0) {
            $subjectId = $this->subjects['deen'];
            $subjectTotals[$subjectId] = ($subjectTotals[$subjectId] ?? 0) + $adjustment;

            RewardPointsLedger::query()->create([
                'student_id' => $student->id,
                'source_type' => 'adjustment',
                'source_id' => crc32(self::FAMILY_REFERENCE.'-'.$childKey),
                'points_delta' => $adjustment,
                'granted_by' => $teacher->id,
                'granted_at' => CarbonImmutable::parse('2026-06-20')->toDateTimeString(),
                'academic_year_id' => $academicYearId,
                'subject_id' => $subjectId,
                'comment' => self::FAMILY_REFERENCE.' launch-history adjustment '.$childKey,
                'sign' => $adjustment < 0 ? 'minus' : 'plus',
            ]);
        }

        foreach ($subjectTotals as $subjectId => $points) {
            RewardTotal::query()->create([
                'student_id' => $student->id,
                'academic_year_id' => $academicYearId,
                'subject_id' => $subjectId,
                'total_points' => $points,
                'created_at' => now(),
            ]);
        }

        StudentGiftPointsHistory::query()->updateOrCreate(
            ['student_id' => $student->id, 'academic_year_id' => $academicYearId],
            [
                'points' => abs($targetTotal),
                'date' => now()->toDateString(),
                'sign' => $targetTotal < 0 ? 'minus' : 'plus',
            ]
        );
    }

    private function activeStudentSubject(Student $student, int $subjectId): StudentsSubject
    {
        $studentSubject = StudentsSubject::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereHas('gradeLevelSubject', fn ($query) => $query->where('subject_id', $subjectId))
            ->first();

        if (! $studentSubject) {
            throw new RuntimeException("Student {$student->id} is missing active subject {$subjectId}.");
        }

        return $studentSubject;
    }

    private function teacherSubjectClassFor(StudentsSubject $studentSubject, int $subjectId): TeacherSubjectClass
    {
        $teacher = $this->context['teacher'];
        $teacherSubjectClass = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', $teacher->id)
            ->where('class_subject_id', $studentSubject->class_subject_id)
            ->where('subject_id', $subjectId)
            ->whereIn('status', ['active', 'current'])
            ->first();

        if (! $teacherSubjectClass) {
            throw new RuntimeException("Missing active teacher subject class for subject {$subjectId}.");
        }

        return $teacherSubjectClass;
    }

    /**
     * @return list<string>
     */
    private function giftNamesFor(string $childKey): array
    {
        return match ($childKey) {
            'yusuf' => [
                'Sticker Pack', 'Coloring Book', 'Small Toy Car', 'Bubble Wand', 'Favorite Snack Box',
                'Play-Dough Set', 'Story Time Choice', 'Dinosaur Figure', 'Mini Puzzle', 'Park Trip',
                'Water Bottle Sticker', 'Extra Bedtime Story', 'Building Blocks Mini Set', 'Animal Flashcards',
                'Ice Cream Treat', 'Toy Train', 'Art Markers', 'Small Plush', 'Family Game Night', 'Big Toy Set',
            ],
            'maryam' => [
                'Glitter Sticker Set', 'Coloring Pencils', 'Hair Clips', 'Craft Paper Pack', 'Mini Notebook',
                'Bracelet Kit', 'Favorite Dessert', 'Story Book', 'Puzzle Box', 'Park Picnic',
                'Paint Set', 'Cute Water Bottle', 'Doll Accessory', 'Family Baking Time', 'Stationery Pouch',
                'Clay Craft Kit', 'Islamic Story Book', 'Board Game Choice', 'Outfit Accessory', 'Craft Gift Box',
            ],
            default => [
                'Football Cards', 'New Notebook', 'Puzzle Challenge', 'Sports Water Bottle', 'Extra Football Time',
                'Science Experiment Kit', 'Book Choice', 'Drawing Pens', 'Board Game', 'Pizza Night',
                'Football Jersey Item', 'LEGO Mini Set', 'Headphones', 'Museum Trip Ticket', 'Strategy Game',
                'Model Kit', 'Desk Organizer', 'Larger LEGO Set', 'Sports Day Out', 'Dirt Bike Fund',
            ],
        };
    }
}
