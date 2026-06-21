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

    public function test_legacy_blue_primary_color_cookie_does_not_override_brand_gold(): void
    {
        $_COOKIE['admin-primaryColor'] = '#2092EC';

        try {
            $this->assertSame('#c9a24d', Helpers::appClasses()['color']);
        } finally {
            unset($_COOKIE['admin-primaryColor']);
        }
    }

    public function test_non_legacy_primary_color_cookie_can_still_customize_theme(): void
    {
        $_COOKIE['admin-primaryColor'] = '#ffab1d';

        try {
            $this->assertSame('#ffab1d', Helpers::appClasses()['color']);
        } finally {
            unset($_COOKIE['admin-primaryColor']);
        }
    }

    public function test_customizer_blue_swatch_is_not_a_legacy_reset_color(): void
    {
        $adminScripts = file_get_contents(resource_path('views/layouts/sections/scriptsIncludes.blade.php'));
        $frontScripts = file_get_contents(resource_path('views/layouts/sections/scriptsIncludesFront.blade.php'));

        $this->assertStringNotContainsString("'color' => '#2092EC'", $adminScripts);
        $this->assertStringNotContainsString("'color' => '#2092EC'", $frontScripts);
        $this->assertStringContainsString("'color' => '#1684d8'", $adminScripts);
        $this->assertStringContainsString("'color' => '#1684d8'", $frontScripts);
    }
}
