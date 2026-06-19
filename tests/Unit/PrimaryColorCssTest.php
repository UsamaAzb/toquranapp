<?php

namespace Tests\Unit;

use App\Helpers\Helpers;
use Tests\TestCase;

class PrimaryColorCssTest extends TestCase
{
    public function test_to_quran_default_primary_color_uses_website_gold(): void
    {
        $this->assertSame('#c9a24d', config('custom.custom.primaryColor'));
    }

    public function test_to_quran_gold_primary_color_uses_brand_contrast(): void
    {
        $css = Helpers::generatePrimaryColorCSS('#c9a24d');

        $this->assertStringContainsString('--bs-primary: #c9a24d;', $css);
        $this->assertStringContainsString('--bs-primary-contrast: #46412f;', $css);
    }
}
