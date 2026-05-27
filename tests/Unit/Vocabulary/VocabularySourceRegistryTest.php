<?php

namespace Tests\Unit\Vocabulary;

use App\Models\VocabularySet;
use App\Services\Vocabulary\VocabularySourceRegistry;
use Tests\TestCase;

class VocabularySourceRegistryTest extends TestCase
{
    public function test_key_conventions_are_stable(): void
    {
        $registry = app(VocabularySourceRegistry::class);

        $this->assertSame('difficulty:4', $registry->difficultyKey(4));
        $this->assertSame('group:8', $registry->groupKey(8));
        $this->assertSame('phonics:category:3', $registry->phonicsKey(3));
        $this->assertSame('cambridge:2', $registry->cambridgeCategoryKey(2));
        $this->assertSame('cambridge:2:unit:4', $registry->cambridgeUnitKey(2, 4));
        $this->assertSame('cambridge:2:unit:4:lesson:1', $registry->cambridgeLessonKey(2, 4, 1));
        $this->assertSame('hangman:6', $registry->hangmanKey(6));
        $this->assertSame('surprise:safe', $registry->surpriseKey('safe'));
    }

    public function test_folder_and_playable_helpers_guard_launchability(): void
    {
        $folder = new VocabularySet([
            'node_type' => VocabularySet::NODE_FOLDER,
            'visibility' => VocabularySet::VISIBILITY_SYSTEM,
        ]);

        $playable = new VocabularySet([
            'node_type' => VocabularySet::NODE_PLAYABLE,
            'visibility' => VocabularySet::VISIBILITY_SYSTEM,
        ]);

        $this->assertFalse($folder->canBeLaunched());
        $this->assertTrue($playable->canBeLaunched());
    }
}
