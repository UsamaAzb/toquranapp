<?php

namespace Tests\Unit;

use App\Support\BookingServiceInterest;
use App\Support\BookingSubjectProvisioning;
use PHPUnit\Framework\TestCase;

class BookingServiceInterestTest extends TestCase
{
    public function test_week14_service_aliases_normalize_to_to_quran_services(): void
    {
        $this->assertSame('Quran Memorization', BookingServiceInterest::normalize('IB Private Classes'));
        $this->assertSame('Quranic Arabic', BookingServiceInterest::normalize('SAT / ACT Preparation'));
        $this->assertSame('Quranic Arabic', BookingServiceInterest::normalize('Help Me Read'));
        $this->assertSame('My Deen Journey', BookingServiceInterest::normalize('Help Me Study'));
    }

    public function test_to_quran_public_aliases_normalize_to_canonical_services(): void
    {
        $this->assertSame('Quran Memorization', BookingServiceInterest::normalize('hifz'));
        $this->assertSame('Arabic Language', BookingServiceInterest::normalize('Arabic Language'));
        $this->assertSame('Arabic Language', BookingServiceInterest::normalize('Arabic'));
        $this->assertSame('Quranic Arabic', BookingServiceInterest::normalize('Quranic Arabic'));
        $this->assertSame('My Deen Journey', BookingServiceInterest::normalize('My Deen Journey (Parenting System)'));
        $this->assertSame('Paid Parental Consultation', BookingServiceInterest::normalize('consultation'));
        $this->assertSame('Sanad Ijazah', BookingServiceInterest::normalize('ijazah'));
        $this->assertSame('Sanad Ijazah', BookingServiceInterest::normalize('Sanad Ijazah Program'));
    }

    public function test_service_interests_map_only_lms_subject_services_to_subject_ids(): void
    {
        $this->assertSame(
            [
                BookingSubjectProvisioning::SUBJECT_QURAN_MEMORIZATION,
                BookingSubjectProvisioning::SUBJECT_ARABIC_LANGUAGE,
                BookingSubjectProvisioning::SUBJECT_SANAD_PROGRAM,
                BookingSubjectProvisioning::SUBJECT_MY_DEEN_JOURNEY,
            ],
            BookingSubjectProvisioning::subjectIdsForServiceInterests([
                'Quran Memorization',
                'Arabic Language',
                'Sanad Ijazah Program',
                'My Deen Journey',
                'Paid Parental Consultation',
            ])
        );

        $this->assertSame([], BookingSubjectProvisioning::subjectIdsForServiceInterests('consultation'));
    }
}
