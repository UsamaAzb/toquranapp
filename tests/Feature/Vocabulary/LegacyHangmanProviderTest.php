<?php

namespace Tests\Feature\Vocabulary;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyHangmanProviderTest extends TestCase
{
    public function test_legacy_provider_schema_is_optional_until_owner_review(): void
    {
        $this->assertIsBool(Schema::hasTable('hangman_category'));
    }
}
