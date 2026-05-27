<?php

namespace Tests\Unit\Vocabulary;

use App\Services\Vocabulary\WrongOptionGenerator;
use Tests\TestCase;

class WrongOptionGeneratorTest extends TestCase
{
    public function test_parse_curated_json_dedupes_and_rejects_correct_answer(): void
    {
        $options = app(WrongOptionGenerator::class)->parseCurated('["kar","car","kar","cer"]', 'car');

        $this->assertSame(['kar', 'cer'], $options);
    }

    public function test_parse_curated_delimited_values(): void
    {
        $options = app(WrongOptionGenerator::class)->parseCurated("finich\nfenish|finish;finush", 'finish');

        $this->assertSame(['finich', 'fenish', 'finush'], $options);
    }

    public function test_spelling_options_use_fallback_when_curated_is_missing(): void
    {
        $options = app(WrongOptionGenerator::class)->spellingOptions('car', null, 1);

        $this->assertNotEmpty($options);
        $this->assertNotContains('car', $options);
    }

    public function test_spelling_options_detailed_reports_rule_labels(): void
    {
        $options = app(WrongOptionGenerator::class)->spellingOptionsDetailed('friend', null, 3, '2');

        $this->assertContains('freind', array_column($options, 'text'));
        $this->assertContains('ie/ei swap', array_column($options, 'label'));
    }

    public function test_informal_rules_are_gated_by_difficulty(): void
    {
        $easy = app(WrongOptionGenerator::class)->spellingOptionsDetailed('running', null, 3, '2');
        $hard = app(WrongOptionGenerator::class)->spellingOptionsDetailed('running', null, 3, '6');

        $this->assertContains('runnin', array_column($easy, 'text'));
        $this->assertNotContains('runnin', array_column($hard, 'text'));
    }
}
