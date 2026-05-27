<?php

namespace Tests\Unit\Vocabulary;

use App\Services\Vocabulary\VocabularyDifficultyEstimator;
use Tests\TestCase;

class VocabularyDifficultyEstimatorTest extends TestCase
{
    public function test_estimate_with_reason_detects_high_frequency_and_compound_words(): void
    {
        $estimator = app(VocabularyDifficultyEstimator::class);

        $cat = $estimator->estimateWithReason('cat');
        $backyard = $estimator->estimateWithReason('backyard');

        $this->assertSame('1', $cat['level']);
        $this->assertStringContainsString('high-frequency', $cat['reason']);
        $this->assertSame('3', $backyard['level']);
        $this->assertStringContainsString('compound word', $backyard['reason']);
    }

    public function test_estimate_with_reason_detects_silent_and_academic_patterns(): void
    {
        $estimator = app(VocabularyDifficultyEstimator::class);

        $calm = $estimator->estimateWithReason('calm');
        $accountability = $estimator->estimateWithReason('accountability');

        $this->assertStringContainsString('silent-letter pattern', $calm['reason']);
        $this->assertSame('6', $accountability['level']);
        $this->assertStringContainsString('academic suffix -ity', $accountability['reason']);
    }

    public function test_short_uncommon_words_are_not_beginner_by_length_alone(): void
    {
        $estimator = app(VocabularyDifficultyEstimator::class);

        $coax = $estimator->estimateWithReason('coax');
        $desk = $estimator->estimateWithReason('desk');

        $this->assertSame('3', $coax['level']);
        $this->assertStringContainsString('short uncommon word', $coax['reason']);
        $this->assertSame('1', $desk['level']);
        $this->assertStringContainsString('starter word', $desk['reason']);
    }

    public function test_common_word_levels_override_spelling_complexity(): void
    {
        $estimator = app(VocabularyDifficultyEstimator::class);

        $writing = $estimator->estimateWithReason('writing');
        $difficult = $estimator->estimateWithReason('difficult');
        $anymore = $estimator->estimateWithReason('anymore');
        $sitDown = $estimator->estimateWithReason('sit down');
        $dont = $estimator->estimateWithReason("don't");

        $this->assertSame('2', $writing['level']);
        $this->assertSame('3', $difficult['level']);
        $this->assertSame('3', $anymore['level']);
        $this->assertSame('1', $sitDown['level']);
        $this->assertSame('1', $dont['level']);
        $this->assertStringContainsString('common word override', $writing['reason']);
    }
}
