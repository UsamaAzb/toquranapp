<?php

namespace Tests\Feature\Vocabulary;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VocabularyManagerTest extends TestCase
{
    public function test_vocabulary_schema_is_owner_managed_for_manager_flows(): void
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            $this->markTestSkipped('Owner-run P7 vocabulary SQL has not been executed in this environment.');
        }

        $this->assertTrue(Schema::hasTable('vocabulary_sets'));
        $this->assertTrue(Schema::hasTable('vocabulary_set_words'));
    }
}
