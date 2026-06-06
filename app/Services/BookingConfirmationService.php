<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingChildEmail;
use App\Support\BookingServiceInterest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingConfirmationService
{
    public function sendConfirmationEmails(Booking $booking, ?BookingChild $bookingChild = null): void
    {
        $resolvedChild = $bookingChild ?? $this->resolveTrackedChild($booking);

        $this->sendConfirmationParentEmail($booking, $resolvedChild);
        $this->sendConfirmationAdminEmail($booking, $resolvedChild);
    }

    public function sendConfirmationParentEmail(Booking $booking, ?BookingChild $bookingChild = null, bool $isResend = false): void
    {
        $resolvedChild = $bookingChild ?? $this->resolveTrackedChild($booking);
        $emailBooking = $this->buildEmailBooking($booking, $resolvedChild);
        $attempt = $this->createAttemptRow($resolvedChild, 'confirmation_parent');
        $supportAddress = config('mail.support_address', config('mail.from.address'));
        $supportName = config('mail.support_name', config('mail.from.name'));

        try {
            Mail::send('emails.consultation-confirmed-parent', ['booking' => $emailBooking], function ($message) use ($emailBooking, $supportAddress, $supportName) {
                $message->from($supportAddress, $supportName)
                    ->to($emailBooking->parent_email, $emailBooking->parent_name)
                    ->subject('Consultation Booking Confirmed - To Quran');
            });

            $this->markAttemptSent($attempt, $isResend);
        } catch (\Exception $e) {
            $this->markAttemptFailed($attempt, $e->getMessage());
            Log::error('Failed to send parent confirmation email', [
                'booking_id' => $booking->id,
                'child_id' => $resolvedChild?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendConfirmationAdminEmail(Booking $booking, ?BookingChild $bookingChild = null, bool $isResend = false): void
    {
        $resolvedChild = $bookingChild ?? $this->resolveTrackedChild($booking);
        $emailBooking = $this->buildEmailBooking($booking, $resolvedChild);
        $attempt = $this->createAttemptRow($resolvedChild, 'confirmation_admin');
        $supportAddress = config('mail.support_address', config('mail.from.address'));
        $adminAddress = config('mail.admin_notification_address', $supportAddress);
        $adminName = config('mail.admin_notification_name', config('mail.support_name', config('mail.from.name')));

        try {
            Mail::send('emails.consultation-scheduled-admin-confirmed', ['booking' => $emailBooking], function ($message) use ($emailBooking, $supportAddress, $adminAddress, $adminName) {
                $message->from($supportAddress, 'To Quran Booking System')
                    ->to($adminAddress, $adminName)
                    ->subject('To Quran Consultation Confirmed - '.$emailBooking->booking_reference);
            });

            $this->markAttemptSent($attempt, $isResend);
        } catch (\Exception $e) {
            $this->markAttemptFailed($attempt, $e->getMessage());
            Log::error('Failed to send admin confirmation email', [
                'booking_id' => $booking->id,
                'child_id' => $resolvedChild?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function buildEmailBooking(Booking $booking, ?BookingChild $bookingChild = null): Booking
    {
        $emailBooking = clone $booking;
        $resolvedChild = $bookingChild ?? $this->resolveTrackedChild($booking);

        if ($resolvedChild) {
            $emailBooking->child_name = $resolvedChild->child_name ?: $booking->child_name;
            $emailBooking->child_age = $resolvedChild->child_age ?: $booking->child_age;
            $emailBooking->child_grade = $resolvedChild->child_grade ?: $booking->child_grade;
            $emailBooking->school_system = $resolvedChild->school_system ?: $booking->school_system;
            $emailBooking->current_school = $resolvedChild->current_school ?: $booking->current_school;
            $emailBooking->service_interest = collect($resolvedChild->service_interests ?? [])
                ->map(fn ($service) => BookingServiceInterest::display($service))
                ->filter()
                ->implode(', ') ?: $booking->service_interest;
            $emailBooking->status = $resolvedChild->workflow_status ?: $resolvedChild->consultation_status ?: $booking->status;
            $emailBooking->consultation_date = $resolvedChild->scheduled_date ?: $booking->consultation_date;
            $emailBooking->consultation_time = $resolvedChild->scheduled_time ?: $booking->consultation_time;
            $emailBooking->follow_up_date = $resolvedChild->followup_date ?: $booking->follow_up_date;
            $emailBooking->consultation_type = $this->resolveChildConsultationType($resolvedChild, $booking);
            $emailBooking->meeting_link = $this->resolveChildMeetingLink($resolvedChild, $booking, $emailBooking->consultation_type);
            $emailBooking->meeting_address = $this->resolveChildMeetingAddress($resolvedChild, $booking, $emailBooking->consultation_type);
        }

        $emailBooking->formatted_consultation_date = $this->formatConsultationDate($emailBooking->consultation_date);
        $emailBooking->formatted_consultation_time = $this->formatConsultationTime($emailBooking->consultation_time);
        $emailBooking->formatted_consultation_type = $this->formatConsultationType($emailBooking->consultation_type);

        return $emailBooking;
    }

    protected function resolveChildConsultationType(BookingChild $bookingChild, Booking $booking): ?string
    {
        $childConsultationType = $bookingChild->consultation_type;

        if (blank($childConsultationType) || $childConsultationType === 'undecided') {
            return $booking->consultation_type;
        }

        return $childConsultationType;
    }

    protected function resolveChildMeetingLink(BookingChild $bookingChild, Booking $booking, ?string $consultationType): ?string
    {
        if ($consultationType !== 'online') {
            return null;
        }

        $childLink = $this->normalizeUrl($bookingChild->meeting_link);

        if (filled($childLink)) {
            return $childLink;
        }

        return $this->normalizeUrl($booking->meeting_link);
    }

    protected function resolveChildMeetingAddress(BookingChild $bookingChild, Booking $booking, ?string $consultationType): ?string
    {
        if ($consultationType !== 'in-person') {
            return null;
        }

        return $this->normalizeText($bookingChild->meeting_address)
            ?: $this->normalizeText($booking->meeting_address);
    }

    protected function resolveTrackedChild(Booking $booking): ?BookingChild
    {
        if ($booking->relationLoaded('children')) {
            return $booking->children
                ->sort(fn (BookingChild $left, BookingChild $right): int => [
                    (int) $left->sort_order,
                    (int) $left->id,
                ] <=> [
                    (int) $right->sort_order,
                    (int) $right->id,
                ])
                ->first();
        }

        return $booking->children()->orderBy('sort_order')->orderBy('id')->first();
    }

    protected function createAttemptRow(?BookingChild $bookingChild, string $emailType): ?BookingChildEmail
    {
        return app(BookingChildEmailService::class)->createAttemptRow($bookingChild, $emailType);
    }

    protected function markAttemptSent(?BookingChildEmail $attempt, bool $isResend): void
    {
        app(BookingChildEmailService::class)->markAttemptSent($attempt, $isResend);
    }

    protected function markAttemptFailed(?BookingChildEmail $attempt, string $message): void
    {
        app(BookingChildEmailService::class)->markAttemptFailed($attempt, $message);
    }

    protected function formatConsultationDate($value): string
    {
        if (blank($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    protected function formatConsultationTime($value): string
    {
        if (blank($value)) {
            return '-';
        }

        $normalized = trim((string) $value);

        if (preg_match('/^\d{1,2}\.\d{2}$/', $normalized)) {
            $normalized = str_replace('.', ':', $normalized);
        }

        try {
            return Carbon::parse($normalized)->format('g:i A');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    protected function formatConsultationType($value): string
    {
        if (blank($value) || $value === 'undecided') {
            return '-';
        }

        return str_replace(
            ['Online', 'In Person', 'Zoom/Camera Needed : Video Call'],
            ['Online', 'In-Person', 'Online'],
            ucwords(str_replace(['_', '-'], ' ', (string) $value))
        );
    }

    protected function normalizeUrl($value): ?string
    {
        $normalized = $this->normalizeText($value);

        if (blank($normalized)) {
            return null;
        }

        return filter_var($normalized, FILTER_VALIDATE_URL) ? $normalized : null;
    }

    protected function normalizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
