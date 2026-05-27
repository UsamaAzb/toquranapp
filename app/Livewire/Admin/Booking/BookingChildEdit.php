<?php

namespace App\Livewire\Admin\Booking;

use App\Livewire\Admin\Booking\Concerns\RecordsAuditLog;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingChildEmail;
use App\Models\GradeLevel;
use App\Models\Services_type;
use App\Services\BookingChildEmailService;
use App\Services\BookingConfirmationService;
use App\Services\BookingTransferService;
use App\Support\BookingServiceInterest;
use App\Support\BookingTransferReadiness;
use App\Support\SchoolSystemOptions;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingChildEdit extends Component
{
    use RecordsAuditLog;

    private const AUDIT_LOG_LIMIT = 20;

    public BookingChild $child;

    #[Locked]
    public ?string $expectedUpdatedAt = null;

    public string $workflowStatus = 'pending';

    public ?string $meetingDisposition = null;

    public ?string $meetingDispositionReason = null;

    public string $evaluationOutcome = 'undecided';

    public string $consultationType = 'undecided';

    public bool $consultationInPerson = false;

    public ?string $meetingLink = null;

    public ?string $meetingAddress = null;

    public ?string $scheduledDate = null;

    public ?string $scheduledTime = null;

    public ?string $followupDate = null;

    public ?string $currentSchool = null;

    public ?string $schoolSystem = null;

    public ?string $notes = null;

    public array $serviceInterests = [];

    #[Locked]
    public ?string $returnUrl = null;

    public bool $showTransferModal = false;

    public ?string $transferContactUpdateNote = null;

    public bool $auditTrailLoaded = false;

    public int $auditTrailTotal = 0;

    public array $auditTrailEntries = [];

    public function mount(BookingChild $bookingChild): void
    {
        $this->child = $bookingChild->loadMissing('booking', 'updatedByUser');
        $this->returnUrl = request()->query('return');

        $this->syncFormFromChild($this->child);
        $this->showTransferModal = request()->boolean('openTransfer') && $this->canTransfer();
    }

    public function render()
    {
        $emailStatuses = $this->emailStatuses();
        $auditLogs = collect($this->auditTrailEntries)->map(function (array $entry): object {
            $changedAt = filled($entry['changed_at_iso'] ?? null)
                ? Carbon::parse((string) $entry['changed_at_iso'])
                : null;

            return (object) [
                'field_name' => $entry['field_name'] ?? null,
                'from_value' => $entry['from_value'] ?? null,
                'to_value' => $entry['to_value'] ?? null,
                'changedBy' => filled($entry['changed_by_name'] ?? null)
                    ? (object) ['name' => $entry['changed_by_name']]
                    : null,
                'changed_at' => $changedAt,
            ];
        });

        return view('livewire.admin.booking.booking-child-edit', [
            'booking' => $this->child->booking,
            'workflowOptions' => $this->workflowOptions(),
            'meetingDispositionOptions' => $this->meetingDispositionOptions(),
            'evaluationOptions' => $this->evaluationOptions(),
            'gradeTitles' => $this->gradeTitles(),
            'serviceOptions' => $this->serviceOptions(),
            'schoolSystemOptions' => SchoolSystemOptions::labels(),
            'originalBookingServiceValues' => $this->originalBookingServiceValues(),
            'originalBookingServiceInterests' => $this->originalBookingServiceInterests(),
            'originalBookingConsultationTypeLabel' => $this->originalBookingConsultationTypeLabel(),
            'canChooseEvaluationOutcome' => $this->canChooseEvaluationOutcome(),
            'canTransfer' => $this->canTransfer(),
            'transferBlockedReason' => $this->transferBlockedReason(),
            'canConfirmLinkedParentContactUpdate' => $this->canConfirmLinkedParentContactUpdate(),
            'rawTransferServices' => $this->rawTransferServices(),
            'unresolvedTransferServices' => $this->unresolvedTransferServices(),
            'emailStatuses' => $emailStatuses,
            'auditLogs' => $auditLogs,
            'auditTotal' => $this->auditTrailTotal,
            'auditLogLimit' => self::AUDIT_LOG_LIMIT,
            'auditTrailLoaded' => $this->auditTrailLoaded,
        ])->layout('components.layouts.app', ['title' => 'Edit Child Booking']);
    }

    public function loadAuditTrail(): void
    {
        if ($this->auditTrailLoaded) {
            return;
        }

        $this->refreshAuditTrail();
    }

    protected function rules(): array
    {
        return [
            'workflowStatus' => ['required', 'in:pending,confirmed,cancelled,questionnaire_sent,questionnaire_answer_received,followup_required'],
            'meetingDisposition' => ['nullable', 'in:completed,cancelled,no_meeting_required'],
            'meetingDispositionReason' => ['nullable', 'string', 'max:500'],
            'evaluationOutcome' => ['required', 'in:undecided,fit,unfit,PL'],
            'consultationType' => ['required', 'in:online,in-person,undecided'],
            'meetingLink' => ['nullable', 'url'],
            'meetingAddress' => ['nullable', 'string', 'max:1000'],
            'scheduledDate' => ['nullable', 'date', 'required_if:workflowStatus,confirmed'],
            'scheduledTime' => ['nullable', 'date_format:H:i', 'required_if:workflowStatus,confirmed', function (string $attribute, mixed $value, \Closure $fail): void {
                if (blank($value)) {
                    return;
                }

                try {
                    $minutes = (int) Carbon::createFromFormat('H:i', (string) $value)->format('i');
                } catch (\Throwable) {
                    return;
                }

                if (! in_array($minutes, [0, 15, 30, 45], true)) {
                    $fail('Scheduled time must use 15-minute intervals.');
                }
            }],
            'followupDate' => ['nullable', 'date', 'required_if:workflowStatus,followup_required'],
            'currentSchool' => ['required', 'string', 'max:255'],
            'schoolSystem' => ['required', 'in:'.implode(',', SchoolSystemOptions::values())],
            'notes' => ['nullable', 'string'],
            'serviceInterests' => ['required', 'array', 'min:1'],
            'serviceInterests.*' => ['string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'currentSchool.required' => 'Current School is required before saving.',
            'schoolSystem.required' => 'School System is required before saving.',
            'schoolSystem.in' => 'Choose one of the active school system options.',
            'serviceInterests.required' => 'Select at least one service interest before saving.',
            'serviceInterests.min' => 'Select at least one service interest before saving.',
        ];
    }

    public function updatedWorkflowStatus(string $value): void
    {
        if ($value === 'followup_required' && $this->evaluationOutcome === 'PL') {
            $this->evaluationOutcome = 'undecided';
        }

        if ($value === 'cancelled') {
            $this->meetingDisposition = null;
            $this->meetingDispositionReason = null;
            $this->evaluationOutcome = 'undecided';
            $this->scheduledDate = null;
            $this->scheduledTime = null;
            $this->followupDate = null;

            return;
        }

        if ($value === 'pending' && blank($this->meetingDisposition)) {
            $this->evaluationOutcome = 'undecided';
        }

        if ($value !== 'confirmed' && $this->meetingDisposition === 'completed') {
            $this->meetingDisposition = null;
        }

        if (! in_array($value, ['confirmed', 'followup_required'], true) && $this->meetingDisposition === 'cancelled') {
            $this->meetingDisposition = null;
        }
    }

    public function updatedEvaluationOutcome(string $value): void
    {
        if ($value === 'PL' && $this->workflowStatus === 'followup_required') {
            $this->workflowStatus = 'pending';
        }
    }

    public function updatedMeetingDisposition(?string $value): void
    {
        $value = filled($value) ? $value : null;
        $this->meetingDisposition = $value;

        if ($value === null) {
            $this->evaluationOutcome = 'undecided';
            $this->meetingDispositionReason = null;

            return;
        }

        if ($value !== 'no_meeting_required') {
            $this->meetingDispositionReason = null;
        }
    }

    public function updatedConsultationInPerson(bool $value): void
    {
        $this->consultationType = $value ? 'in-person' : 'online';
    }

    public function selectConsultationType(string $value): void
    {
        if (! in_array($value, ['online', 'in-person', 'undecided'], true)) {
            return;
        }

        $this->consultationType = $value;
        $this->consultationInPerson = $value === 'in-person';
    }

    public function updatedConsultationType(string $value): void
    {
        $this->consultationInPerson = $value === 'in-person';
    }

    public function save(): void
    {
        $this->resetErrorBag('stale');
        $this->validate();

        if (! $this->passesCrossStatusValidation()) {
            return;
        }

        $shouldSendConfirmation = false;

        $freshChild = DB::transaction(function () use (&$shouldSendConfirmation) {
            $lockedChild = BookingChild::query()
                ->with('booking')
                ->lockForUpdate()
                ->findOrFail($this->child->id);

            if ($this->expectedUpdatedAt !== optional($lockedChild->updated_at)?->toISOString()) {
                $this->addError('stale', 'This child record was updated by another admin. Reload the latest values before saving.');

                return null;
            }

            $storedWorkflowStatus = $this->resolvedWorkflowStatus($lockedChild);

            if (
                $storedWorkflowStatus !== $this->workflowStatus
                && in_array($this->workflowStatus, ['questionnaire_sent', 'questionnaire_answer_received'], true)
            ) {
                $this->addError(
                    'workflowStatus',
                    'Questionnaire workflow states are reserved for the later questionnaire sprint and are not editable through the Sprint 3 booking admin.'
                );

                return null;
            }

            $oldValues = $this->trackedFieldValues($lockedChild);
            $lockedChild->fill($this->payload());
            $lockedChild->save();
            $lockedChild->refresh();
            $lockedChild->loadMissing('booking', 'updatedByUser');

            $this->logTrackedChanges($lockedChild, $oldValues);
            $shouldSendConfirmation = $this->shouldSendConfirmationEmail($oldValues, $lockedChild);

            return $lockedChild;
        });

        if (! $freshChild instanceof BookingChild) {
            return;
        }

        if ($shouldSendConfirmation) {
            app(BookingConfirmationService::class)->sendConfirmationEmails($freshChild->booking, $freshChild);
        }

        $this->child = $freshChild;
        $this->syncFormFromChild($freshChild);
        $this->refreshAuditTrailIfLoaded();

        session()->flash('success', 'Child record updated.');
        $this->dispatch('child-saved', childId: $freshChild->id);
    }

    public function cancelUrl(): string
    {
        if (filled($this->returnUrl) && $this->isInternalUrl($this->returnUrl)) {
            return $this->returnUrl;
        }

        return route('admin.bookings.livewire');
    }

    public function parentEditUrl(): ?string
    {
        if (! $this->child->booking) {
            return null;
        }

        return route('admin.bookings.parent.edit', [
            'booking' => $this->child->booking->id,
            'return' => route('admin.bookings.children.edit', [
                'bookingChild' => $this->child->id,
                'return' => $this->cancelUrl(),
            ]),
        ]);
    }

    public function openTransferModal(): void
    {
        $this->resetErrorBag('transfer');

        if ($blockedReason = $this->transferBlockedReason()) {
            $this->showTransferModal = false;
            $this->addError('transfer', $blockedReason);

            return;
        }

        $this->showTransferModal = true;
    }

    public function cancelTransferModal(): void
    {
        $this->showTransferModal = false;
    }

    public function confirmLinkedParentContactUpdate(int $childId): void
    {
        $this->resetErrorBag('transferContactUpdateNote');
        $this->resetErrorBag('transfer');

        if ((int) $childId !== (int) $this->child->id) {
            $this->addError('transfer', 'This editor is no longer focused on the selected child.');

            return;
        }

        $note = $this->normalizedText($this->transferContactUpdateNote);

        if ($note === null) {
            $this->addError('transferContactUpdateNote', 'Add a note before updating the linked parent contact.');

            return;
        }

        try {
            app(BookingTransferService::class)->confirmLinkedParentContactUpdate($this->child, $note);
        } catch (InvalidArgumentException $exception) {
            $this->addError('transfer', $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('transfer', 'Linked parent contact update failed. Please review the parent contact before retrying.');

            return;
        }

        $freshChild = BookingChild::query()
            ->with('booking', 'updatedByUser')
            ->findOrFail($childId);

        $this->child = $freshChild;
        $this->syncFormFromChild($freshChild);
        $this->transferContactUpdateNote = null;

        session()->flash('success', 'Linked parent contact updated. Transfer can be retried.');
    }

    public function transfer(int $childId): void
    {
        $this->resetErrorBag('transfer');
        $this->showTransferModal = false;

        $child = BookingChild::query()
            ->with('booking')
            ->findOrFail($childId);

        if ((int) $child->id !== (int) $this->child->id) {
            $this->addError('transfer', 'This editor is no longer focused on the selected child.');

            return;
        }

        if ($blockedReason = BookingTransferReadiness::blockedReason($child, $child->booking)) {
            $this->child = $child->loadMissing('updatedByUser');
            $this->syncFormFromChild($this->child);
            $this->addError('transfer', $blockedReason);

            return;
        }

        $oldTransferStatus = $child->transfer_status;
        $oldStudentId = $child->student_id;

        try {
            $result = app(BookingTransferService::class)->transferChild($child);
        } catch (InvalidArgumentException $exception) {
            $this->addError('transfer', $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('transfer', 'Transfer failed. Please try again or review the child data before retrying.');

            return;
        }

        $freshChild = BookingChild::query()
            ->with('booking', 'updatedByUser')
            ->findOrFail($childId);

        if ($oldTransferStatus !== $freshChild->transfer_status) {
            $this->logChildChange($freshChild, 'transfer_status', $oldTransferStatus, $freshChild->transfer_status);
        }

        if ($oldStudentId !== $freshChild->student_id) {
            $this->logChildChange($freshChild, 'student_id', $oldStudentId, $freshChild->student_id);
        }

        $this->child = $freshChild;
        $this->syncFormFromChild($freshChild);
        $this->refreshAuditTrailIfLoaded();

        $workspaceUrl = $result['family_workspace_url'] ?? (
            ($result['parent_id'] ?? $freshChild->booking?->parent_id)
                ? route('admin.families.show', $result['parent_id'] ?? $freshChild->booking?->parent_id)
                : null
        );

        session()->flash('success', 'Child transferred successfully. Open the Family Workspace to activate the family and child accounts.');
        if ($workspaceUrl) {
            session()->flash('family_workspace_url', $workspaceUrl);
        }

        $this->dispatch(
            'child-transferred',
            childId: $freshChild->id,
            parentId: $result['parent_id'] ?? $freshChild->booking?->parent_id,
            studentId: $result['student_id'] ?? $freshChild->student_id
        );

        $this->redirectRoute('admin.bookings.transferred', navigate: true);
    }

    public function resendEmail(int $childId, string $emailType): void
    {
        $this->resetErrorBag('email');
        $draft = $this->draftState();

        if ((int) $childId !== (int) $this->child->id) {
            $this->addError('email', 'This editor is no longer focused on the selected child.');

            return;
        }

        $child = BookingChild::query()
            ->with([
                'booking',
                'updatedByUser',
                'emails' => fn ($query) => $query
                    ->orderByDesc('last_attempt_at')
                    ->orderByDesc('id'),
            ])
            ->findOrFail($childId);

        try {
            app(BookingChildEmailService::class)->resend($child, $emailType);
        } catch (InvalidArgumentException $exception) {
            $this->addError('email', $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('email', 'Resend failed unexpectedly. Please try again.');

            return;
        }

        $freshChild = BookingChild::query()
            ->with([
                'booking',
                'updatedByUser',
                'emails' => fn ($query) => $query
                    ->orderByDesc('last_attempt_at')
                    ->orderByDesc('id'),
            ])
            ->findOrFail($childId);

        $this->child = $freshChild;
        $this->syncFormFromChild($freshChild);
        $this->restoreDraftState($draft);

        if (session()->has('info')) {
            return;
        }

        $latestStatus = app(BookingChildEmailService::class)->latestStatus($freshChild, $emailType);
        $emailLabel = $this->emailTypeLabel($emailType);

        if ($latestStatus?->status === 'failed') {
            $message = 'Resend failed again for '.$emailLabel.'.';

            if (filled($latestStatus->last_error_message)) {
                $message .= ' '.$latestStatus->last_error_message;
            }

            $this->addError('email', $message);

            return;
        }

        session()->flash('success', $emailLabel.' resend completed.');
    }

    public function currentWorkflowStatusLabel(): string
    {
        return match ($this->workflowStatus) {
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled / Closed',
            'questionnaire_sent' => 'Questionnaire Sent (Reserved)',
            'questionnaire_answer_received' => 'Questionnaire Answer Received (Reserved)',
            'followup_required' => 'Follow-Up Required',
            default => 'Pending',
        };
    }

    public function canTransfer(): bool
    {
        return BookingTransferReadiness::canTransfer($this->child, $this->child->booking);
    }

    public function transferBlockedReason(): ?string
    {
        return BookingTransferReadiness::blockedReason($this->child, $this->child->booking);
    }

    public function canConfirmLinkedParentContactUpdate(): bool
    {
        return BookingTransferReadiness::canConfirmLinkedParentContactUpdate($this->child, $this->child->booking);
    }

    public function rawTransferServices(): array
    {
        return BookingTransferReadiness::effectiveRawServiceValues($this->child, $this->child->booking);
    }

    public function unresolvedTransferServices(): array
    {
        return BookingTransferReadiness::unresolvedServiceValues($this->child, $this->child->booking);
    }

    public function schoolSystemLabel(mixed $value): string
    {
        return SchoolSystemOptions::display($value) ?? '-';
    }

    public function emailTypeLabel(string $emailType): string
    {
        return app(BookingChildEmailService::class)->emailTypeLabel($emailType);
    }

    public function emailStatusBadge(?BookingChildEmail $status, ?string $emailType = null): array
    {
        return app(BookingChildEmailService::class)->emailStatusBadge($status, $emailType);
    }

    public function isRetiredEmailType(string $emailType): bool
    {
        return app(BookingChildEmailService::class)->isRetiredEmailType($emailType);
    }

    public function formatEmailTimestamp(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d M Y g:i A');
        }

        try {
            return Carbon::parse((string) $value)->format('d M Y g:i A');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function canResendEmailType(string $emailType, ?BookingChildEmail $status = null): bool
    {
        if ($this->isRetiredEmailType($emailType)) {
            return false;
        }

        if (($status?->status ?? 'not_sent') !== 'failed') {
            return false;
        }

        return match ($emailType) {
            'confirmation_parent', 'confirmation_admin' => true,
            default => false,
        };
    }

    protected function payload(): array
    {
        $workflowStatus = $this->workflowStatus;
        $evaluationOutcome = $this->evaluationOutcome;
        $consultationType = $this->consultationType;
        $meetingDisposition = filled($this->meetingDisposition) ? $this->meetingDisposition : null;
        $meetingDispositionReason = $meetingDisposition === 'no_meeting_required'
          ? $this->normalizedText($this->meetingDispositionReason)
          : null;
        $meetingLink = $consultationType === 'online'
          ? $this->normalizedText($this->meetingLink)
          : null;
        $meetingAddress = $consultationType === 'in-person'
          ? $this->normalizedText($this->meetingAddress)
          : null;

        return [
            'workflow_status' => $workflowStatus,
            'meeting_disposition' => $meetingDisposition,
            'meeting_disposition_reason' => $meetingDispositionReason,
            'evaluation_outcome' => $evaluationOutcome,
            'consultation_type' => $consultationType,
            'meeting_link' => $meetingLink,
            'meeting_address' => $meetingAddress,
            'scheduled_date' => $this->shouldStoreScheduledFields() && filled($this->scheduledDate) ? $this->scheduledDate : null,
            'scheduled_time' => $this->shouldStoreScheduledFields() && filled($this->scheduledTime) ? $this->scheduledTime : null,
            'followup_date' => $this->shouldStoreFollowupDate() && filled($this->followupDate)
              ? $this->normalizedDateTimeValue($this->followupDate)
              : null,
            'service_interests' => $this->normalizedServiceInterests(),
            'current_school' => $this->normalizedText($this->currentSchool),
            'school_system' => SchoolSystemOptions::normalize($this->schoolSystem),
            'notes' => $this->normalizedText($this->notes),
            'updated_by' => auth()->id(),
            // Legacy compatibility until the booking-level fallback path is retired.
            'consultation_status' => $this->legacyConsultationStatus($workflowStatus, $meetingDisposition),
            'evaluation_status' => $this->legacyEvaluationStatus($evaluationOutcome),
        ];
    }

    protected function shouldSendConfirmationEmail(array $oldValues, BookingChild $freshChild): bool
    {
        $confirmationInputsChanged = collect([
            'scheduled_date',
            'scheduled_time',
            'consultation_type',
            'meeting_link',
            'meeting_address',
        ])->contains(fn (string $field) => $this->normalizedComparableValue($oldValues[$field] ?? null) !== $this->normalizedComparableValue($freshChild->{$field}));

        return ($oldValues['workflow_status'] ?? null) !== 'confirmed' && $freshChild->workflow_status === 'confirmed'
          || (($oldValues['workflow_status'] ?? null) === 'confirmed'
            && $freshChild->workflow_status === 'confirmed'
            && $confirmationInputsChanged);
    }

    protected function logTrackedChanges(BookingChild $freshChild, array $oldValues): void
    {
        foreach (array_keys($oldValues) as $field) {
            $from = $oldValues[$field] ?? null;
            $to = $freshChild->{$field};

            if ($this->normalizedComparableValue($from) === $this->normalizedComparableValue($to)) {
                continue;
            }

            $this->logChildChange($freshChild, $field, $from, $to);
        }
    }

    protected function trackedFieldValues(BookingChild $child): array
    {
        return [
            'workflow_status' => $child->workflow_status,
            'meeting_disposition' => $child->meeting_disposition,
            'meeting_disposition_reason' => $child->meeting_disposition_reason,
            'evaluation_outcome' => $child->evaluation_outcome,
            'consultation_type' => $child->consultation_type,
            'meeting_link' => $child->meeting_link,
            'meeting_address' => $child->meeting_address,
            'scheduled_date' => $child->scheduled_date,
            'scheduled_time' => $child->scheduled_time,
            'followup_date' => $child->followup_date,
            'service_interests' => $child->service_interests ?? [],
            'current_school' => $child->current_school,
            'school_system' => $child->school_system,
            'notes' => $child->notes,
        ];
    }

    protected function syncFormFromChild(BookingChild $child): void
    {
        $booking = $child->booking;

        $this->workflowStatus = $this->resolvedWorkflowStatus($child);
        $this->meetingDisposition = $child->meeting_disposition;
        $this->meetingDispositionReason = $child->meeting_disposition_reason;
        $this->evaluationOutcome = $this->resolvedEvaluationOutcome($child);
        $this->consultationType = $this->resolvedConsultationType($child, $booking);
        $this->consultationInPerson = $this->consultationType === 'in-person';
        $this->meetingLink = $this->resolvedMeetingLink($child, $booking);
        $this->meetingAddress = $this->resolvedMeetingAddress($child, $booking);
        $this->scheduledDate = $this->formatDateInput($child->scheduled_date ?: $booking?->consultation_date);
        $this->scheduledTime = $this->formatTimeInput($child->scheduled_time ?: $booking?->consultation_time);
        $this->followupDate = $this->formatDateTimeInput($child->followup_date ?: $booking?->follow_up_date);
        $this->currentSchool = $child->current_school ?: $booking?->current_school;
        $this->schoolSystem = SchoolSystemOptions::normalize($child->school_system ?: $booking?->school_system);
        $this->notes = $child->notes;
        $this->serviceInterests = $this->resolvedServiceInterests($child, $booking);
        $this->expectedUpdatedAt = optional($child->updated_at)?->toISOString();
    }

    protected function emailStatuses(): Collection
    {
        return app(BookingChildEmailService::class)->latestStatusesForChild($this->child);
    }

    protected function refreshAuditTrailIfLoaded(): void
    {
        if (! $this->auditTrailLoaded) {
            return;
        }

        $this->refreshAuditTrail();
    }

    protected function refreshAuditTrail(): void
    {
        $logs = $this->child->auditLogs()
            ->with('changedBy')
            ->orderByDesc('changed_at')
            ->limit(self::AUDIT_LOG_LIMIT)
            ->get();

        $this->auditTrailEntries = $logs
            ->map(fn ($log): array => [
                'field_name' => (string) $log->field_name,
                'from_value' => $log->from_value,
                'to_value' => $log->to_value,
                'changed_by_name' => $log->changedBy?->name,
                'changed_at_iso' => $log->changed_at?->toISOString(),
            ])
            ->all();
        $this->auditTrailTotal = $this->child->auditLogs()->count();
        $this->auditTrailLoaded = true;
    }

    protected function draftState(): array
    {
        return [
            'expectedUpdatedAt' => $this->expectedUpdatedAt,
            'workflowStatus' => $this->workflowStatus,
            'meetingDisposition' => $this->meetingDisposition,
            'meetingDispositionReason' => $this->meetingDispositionReason,
            'evaluationOutcome' => $this->evaluationOutcome,
            'consultationType' => $this->consultationType,
            'consultationInPerson' => $this->consultationInPerson,
            'meetingLink' => $this->meetingLink,
            'meetingAddress' => $this->meetingAddress,
            'scheduledDate' => $this->scheduledDate,
            'scheduledTime' => $this->scheduledTime,
            'followupDate' => $this->followupDate,
            'currentSchool' => $this->currentSchool,
            'schoolSystem' => $this->schoolSystem,
            'notes' => $this->notes,
            'serviceInterests' => $this->serviceInterests,
        ];
    }

    protected function restoreDraftState(array $draft): void
    {
        foreach ($draft as $field => $value) {
            if (! property_exists($this, $field)) {
                continue;
            }

            $this->{$field} = $value;
        }
    }

    protected function resolvedWorkflowStatus(BookingChild $child): string
    {
        $status = $child->workflow_status ?: $child->consultation_status;

        return match ($status) {
            'confirmed' => 'confirmed',
            'cancelled' => 'cancelled',
            'questionnaire_sent' => 'questionnaire_sent',
            'questionnaire_answer_received' => 'questionnaire_answer_received',
            'followup', 'followup_required' => 'followup_required',
            default => 'pending',
        };
    }

    protected function resolvedEvaluationOutcome(BookingChild $child): string
    {
        return $child->evaluation_outcome
          ?: ($child->evaluation_status ?: 'undecided');
    }

    protected function resolvedConsultationType(BookingChild $child, ?Booking $booking): string
    {
        $consultationType = $child->consultation_type;

        if (blank($consultationType) || $consultationType === 'undecided') {
            $consultationType = $booking?->consultation_type;
        }

        return filled($consultationType) ? (string) $consultationType : 'undecided';
    }

    protected function resolvedMeetingLink(BookingChild $child, ?Booking $booking): ?string
    {
        $childLink = $this->normalizedText($child->meeting_link);

        if (filled($childLink)) {
            return $childLink;
        }

        $bookingLink = $this->normalizedText($booking?->meeting_link);

        if (blank($bookingLink)) {
            return null;
        }

        return filter_var($bookingLink, FILTER_VALIDATE_URL) ? $bookingLink : null;
    }

    protected function resolvedMeetingAddress(BookingChild $child, ?Booking $booking): ?string
    {
        return $this->normalizedText($child->meeting_address)
          ?: $this->normalizedText($booking?->meeting_address);
    }

    protected function resolvedServiceInterests(BookingChild $child, ?Booking $booking): array
    {
        unset($booking);

        return collect($child->service_interests ?? [])
            ->map(fn ($value) => BookingServiceInterest::normalize(is_string($value) ? trim($value) : $value))
            ->filter()
            ->values()
            ->all();
    }

    protected function workflowOptions(): array
    {
        $options = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled / Closed',
            'followup_required' => 'Follow-Up Required',
        ];

        if (in_array($this->workflowStatus, ['questionnaire_sent', 'questionnaire_answer_received'], true)) {
            $options = [$this->workflowStatus => $this->currentWorkflowStatusLabel()] + $options;
        }

        return $options;
    }

    protected function shouldStoreFollowupDate(): bool
    {
        return $this->workflowStatus === 'followup_required'
          || $this->evaluationOutcome === 'PL'
          || filled($this->followupDate);
    }

    protected function shouldStoreScheduledFields(): bool
    {
        return $this->workflowStatus === 'confirmed'
          || $this->workflowStatus === 'followup_required'
          || in_array($this->meetingDisposition, ['completed', 'cancelled'], true)
          || filled($this->scheduledDate)
          || filled($this->scheduledTime);
    }

    protected function requiresConsultationDetails(): bool
    {
        return $this->workflowStatus === 'confirmed'
          || in_array($this->meetingDisposition, ['completed', 'cancelled'], true)
          || filled($this->scheduledDate)
          || filled($this->scheduledTime);
    }

    protected function meetingDispositionOptions(): array
    {
        $options = [];
        $currentDisposition = filled($this->meetingDisposition) ? $this->meetingDisposition : null;

        if ($this->workflowStatus === 'pending') {
            $options['no_meeting_required'] = 'No Meeting Required';
        } elseif ($this->workflowStatus === 'confirmed' && $this->canChooseScheduledMeetingDisposition()) {
            $options['completed'] = 'Meeting Completed';
            $options['cancelled'] = 'Meeting Cancelled';
        } elseif ($this->workflowStatus === 'followup_required' && $this->canChooseScheduledMeetingDisposition()) {
            $options['cancelled'] = 'Meeting Cancelled';
        }

        if ($currentDisposition !== null && ! array_key_exists($currentDisposition, $options)) {
            $options = [$currentDisposition => $this->meetingDispositionLabel($currentDisposition)] + $options;
        }

        return $options;
    }

    protected function canChooseScheduledMeetingDisposition(): bool
    {
        return filled($this->scheduledDate)
          && filled($this->scheduledTime);
    }

    public function canChooseEvaluationOutcome(): bool
    {
        if ($this->workflowStatus === 'cancelled') {
            return false;
        }

        return filled($this->meetingDisposition);
    }

    protected function meetingDispositionLabel(string $value): string
    {
        return match ($value) {
            'completed' => 'Meeting Completed',
            'cancelled' => 'Meeting Cancelled',
            'no_meeting_required' => 'No Meeting Required',
            default => $value,
        };
    }

    protected function evaluationOptions(): array
    {
        return [
            'undecided' => 'Undecided',
            'fit' => 'Fit',
            'unfit' => 'Unfit',
            'PL' => 'Potential Later (PL)',
        ];
    }

    protected function consultationTypeOptions(): array
    {
        return [
            'undecided' => 'Undecided',
            'online' => 'Online',
            'in-person' => 'In-Person',
        ];
    }

    protected function gradeTitles(): array
    {
        if (! Schema::hasTable('grade_levels')) {
            return [];
        }

        return GradeLevel::query()
            ->when(Schema::hasColumn('grade_levels', 'active'), fn ($query) => $query->orderByDesc('active'))
            ->orderBy('level_order')
            ->orderBy('id')
            ->pluck('title', 'id')
            ->all();
    }

    protected function serviceOptions(): Collection
    {
        $currentValues = collect($this->serviceInterests)
            ->map(fn ($value) => BookingServiceInterest::normalize(is_string($value) ? trim($value) : $value))
            ->filter()
            ->values();

        $fallback = collect([
            ['value' => 'Quran Memorization', 'label' => 'Quran Memorization'],
            ['value' => 'Quranic Arabic', 'label' => 'Quranic Arabic'],
            ['value' => 'My Deen Journey', 'label' => 'My Deen Journey'],
            ['value' => 'Paid Parental Consultation', 'label' => 'Paid Parental Consultation'],
            ['value' => 'Sanad Ijazah', 'label' => 'Sanad Ijazah'],
        ]);

        if (Schema::hasTable('services_types')) {
            $hasConfiguredServices = Services_type::query()->exists();
            $query = Services_type::query();

            if (Schema::hasColumn('services_types', 'active')) {
                $query->where(function ($activeQuery) {
                    $activeQuery->whereNull('active')
                        ->orWhere('active', 1);
                });
            }

            $options = $query->orderBy('title')->get(['title', 'value'])
                ->map(fn (Services_type $service) => [
                    'value' => BookingServiceInterest::normalize($service->value) ?? $service->value,
                    'label' => $service->title,
                ]);

            $missingCurrentValues = $currentValues
                ->reject(fn (string $value) => $options->contains(fn (array $option) => $option['value'] === $value))
                ->map(fn (string $value) => [
                    'value' => $value,
                    'label' => BookingServiceInterest::display($value),
                ]);

            $configuredOptions = $options
                ->merge($missingCurrentValues)
                ->filter(fn (array $option) => BookingServiceInterest::isChildFacingOption($option))
                ->unique('value')
                ->values();

            if ($hasConfiguredServices) {
                return $configuredOptions;
            }
        }

        return $fallback->merge(
            $currentValues
                ->map(fn (string $value) => [
                    'value' => $value,
                    'label' => BookingServiceInterest::display($value),
                ])
        )->filter(fn (array $option) => BookingServiceInterest::isChildFacingOption($option))
            ->unique('value')
            ->values();
    }

    protected function originalBookingServiceInterests(): array
    {
        if (! filled($this->child->booking?->service_interest)) {
            return [];
        }

        return collect(explode(',', (string) $this->child->booking->service_interest))
            ->map(fn (string $value) => BookingServiceInterest::display(trim($value)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function originalBookingServiceValues(): array
    {
        if (! filled($this->child->booking?->service_interest)) {
            return [];
        }

        return collect(explode(',', (string) $this->child->booking->service_interest))
            ->map(fn (string $value) => BookingServiceInterest::normalize(trim($value)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function originalBookingConsultationTypeLabel(): ?string
    {
        return match ($this->child->booking?->consultation_type) {
            'online' => 'Online',
            'in-person' => 'In-person',
            default => null,
        };
    }

    protected function normalizedServiceInterests(): array
    {
        return collect($this->serviceInterests)
            ->map(fn ($value) => BookingServiceInterest::normalize(is_string($value) ? trim($value) : $value))
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizedText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function formatDateInput(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return filled($value) ? (string) $value : null;
        }
    }

    protected function formatDateTimeInput(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d\TH:i');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return filled($value) ? (string) $value : null;
        }
    }

    protected function formatTimeInput(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        $normalized = trim((string) $value);

        if (preg_match('/^\d{1,2}\.\d{2}$/', $normalized)) {
            $normalized = str_replace('.', ':', $normalized);
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $normalized)) {
            return $normalized;
        }

        try {
            return Carbon::parse($normalized)->format('H:i');
        } catch (\Throwable) {
            return $normalized !== '' ? $normalized : null;
        }
    }

    protected function normalizedDateTimeValue(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function normalizedComparableValue(mixed $value): mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return collect($value)->values()->all();
        }

        return $value;
    }

    protected function legacyConsultationStatus(string $workflowStatus, ?string $meetingDisposition): string
    {
        if ($meetingDisposition === 'cancelled') {
            return 'cancelled';
        }

        return match ($workflowStatus) {
            'cancelled' => 'cancelled',
            'followup_required' => 'followup',
            default => $workflowStatus,
        };
    }

    protected function legacyEvaluationStatus(string $evaluationOutcome): ?string
    {
        return $evaluationOutcome === 'undecided' ? null : $evaluationOutcome;
    }

    protected function isInternalUrl(string $url): bool
    {
        if (str_starts_with($url, '//')) {
            return false;
        }

        return str_starts_with($url, url('/')) || str_starts_with($url, '/');
    }

    protected function passesCrossStatusValidation(): bool
    {
        $valid = true;
        if ($this->evaluationOutcome === 'PL') {
            if ($this->workflowStatus === 'followup_required') {
                $this->addError('evaluationOutcome', 'Potential Later (PL) cannot be combined with Workflow Status = Follow-Up Required.');
                $valid = false;
            }

            if (blank($this->followupDate)) {
                $this->addError('followupDate', 'A future follow-up date is required when Evaluation Outcome is Potential Later (PL).');
                $valid = false;
            } else {
                try {
                    if (Carbon::parse($this->followupDate)->lessThanOrEqualTo(now())) {
                        $this->addError('followupDate', 'Potential Later (PL) must use a follow-up date in the future.');
                        $valid = false;
                    }
                } catch (\Throwable) {
                    // Base validation already handles malformed date strings.
                }
            }
        }

        if ($this->workflowStatus === 'followup_required' && $this->evaluationOutcome === 'PL') {
            $this->addError('workflowStatus', 'Follow-Up Required cannot be combined with Evaluation Outcome = Potential Later (PL).');
            $valid = false;
        }

        if (! $this->canChooseEvaluationOutcome() && $this->evaluationOutcome !== 'undecided') {
            $this->addError('evaluationOutcome', 'Choose Meeting Disposition before setting Evaluation Outcome.');
            $valid = false;
        }

        if ($this->workflowStatus === 'cancelled') {
            if ($this->meetingDisposition !== null) {
                $this->addError('meetingDisposition', 'Workflow Status = Cancelled / Closed does not use Meeting Disposition.');
                $valid = false;
            }

            if ($this->evaluationOutcome !== 'undecided') {
                $this->addError('evaluationOutcome', 'Workflow Status = Cancelled / Closed does not use Evaluation Outcome.');
                $valid = false;
            }
        }

        if ($this->workflowStatus === 'pending' && ! in_array($this->meetingDisposition, [null, 'no_meeting_required'], true)) {
            $this->addError('meetingDisposition', 'Pending workflow only allows No Meeting Required as a meeting disposition.');
            $valid = false;
        }

        if ($this->meetingDisposition === 'cancelled' && ! in_array($this->workflowStatus, ['confirmed', 'followup_required'], true)) {
            $this->addError('meetingDisposition', 'Meeting Disposition = Cancelled is only valid when Workflow Status is Confirmed or Follow-Up Required.');
            $valid = false;
        }

        if ($this->meetingDisposition === 'completed' && $this->workflowStatus !== 'confirmed') {
            $this->addError('meetingDisposition', 'Meeting Disposition = Completed is only valid when Workflow Status is Confirmed.');
            $valid = false;
        }

        if ($this->workflowStatus === 'followup_required' && $this->meetingDisposition === 'cancelled' && ! $this->canChooseScheduledMeetingDisposition()) {
            $this->addError('meetingDisposition', 'Follow-Up Required can only move to Meeting Cancelled after a scheduled date and time exist.');
            $valid = false;
        }

        if (in_array($this->meetingDisposition, ['completed', 'cancelled'], true)) {
            if (blank($this->scheduledDate)) {
                $this->addError('scheduledDate', 'Scheduled date is required when a meeting was completed or cancelled.');
                $valid = false;
            }

            if (blank($this->scheduledTime)) {
                $this->addError('scheduledTime', 'Scheduled time is required when a meeting was completed or cancelled.');
                $valid = false;
            }
        }

        if ($this->requiresConsultationDetails()) {
            if (! in_array($this->consultationType, ['online', 'in-person'], true)) {
                $this->addError('consultationType', 'Choose Consultation Mode before saving scheduled or recorded meeting details.');
                $valid = false;
            }

            if ($this->consultationType === 'online' && blank($this->meetingLink)) {
                $this->addError('meetingLink', 'Meeting link is required for online consultations once the meeting is being scheduled or recorded.');
                $valid = false;
            }

            if ($this->consultationType === 'in-person' && blank($this->meetingAddress)) {
                $this->addError('meetingAddress', 'Meeting address is required for in-person consultations once the meeting is being scheduled or recorded.');
                $valid = false;
            }
        }

        return $valid;
    }
}
