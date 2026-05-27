<?php

namespace Tests\Unit\Vocabulary;

use App\Services\Vocabulary\LegacyHangmanImportService;
use Tests\TestCase;

class LegacyHangmanImportServiceTest extends TestCase
{
    public function test_legacy_report_returns_a_collection(): void
    {
        $this->assertIsIterable(app(LegacyHangmanImportService::class)->report());
    }
}
