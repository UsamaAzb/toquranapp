<?php

namespace Tests\Unit\Vocabulary;

use App\Services\Vocabulary\WrongOptionGenerator;
use Tests\TestCase;

class SpellingChoiceGameTest extends TestCase
{
    public function test_curated_spelling_suggestions_are_used_before_fallback(): void
    {
        $options = app(WrongOptionGenerator::class)->spellingOptions('car', 'kar,cer', 2);

        $this->assertSame(['kar', 'cer'], $options);
    }

    public function test_spelling_suggestions_do_not_duplicate_correct_word(): void
    {
        $options = app(WrongOptionGenerator::class)->spellingOptions('phone', 'phone,fone', 2);

        $this->assertNotContains('phone', $options);
        $this->assertCount(count(array_unique(array_map('strtolower', $options))), $options);
    }
}
