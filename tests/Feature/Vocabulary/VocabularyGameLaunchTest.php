<?php

namespace Tests\Feature\Vocabulary;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VocabularyGameLaunchTest extends TestCase
{
    public function test_game_launch_schema_is_owner_managed(): void
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            $this->markTestSkipped('Owner-run P7 vocabulary SQL has not been executed in this environment.');
        }

        $this->assertTrue(Schema::hasTable('vocabulary_sets'));
        $this->assertTrue(Schema::hasTable('vocabulary_game_assignments'));
    }
}
