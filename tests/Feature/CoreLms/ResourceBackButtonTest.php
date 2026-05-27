<?php

namespace Tests\Feature\CoreLms;

use Tests\TestCase;

class ResourceBackButtonTest extends TestCase
{
    public function test_resource_back_script_uses_safe_same_tab_marker_and_explicit_fallback(): void
    {
        $script = file_get_contents(resource_path('views/front/partials/resource-back-script.blade.php'));

        $this->assertStringContainsString('w14.resource.sameTabReferrer', $script);
        $this->assertStringContainsString('window.history.back()', $script);
        $this->assertStringContainsString('button.dataset.returnUrl', $script);
        $this->assertStringContainsString('return_to', $script);
        $this->assertStringContainsString('window.location.assign(target)', $script);
        $this->assertStringContainsString("hasAnyRole(['student', 'parent'])", $script);
        $this->assertStringContainsString("'.back_but, .w14-assigned-resource-back'", $script);
        $this->assertStringContainsString('button.remove()', $script);
        $this->assertStringNotContainsString('aria-disabled', str_replace('removeAttribute(\'aria-disabled\')', '', $script));
        $this->assertStringNotContainsString('pointerEvents = \'none\'', $script);

        $historyBackPosition = strpos($script, 'window.history.back()');
        $explicitReturnPosition = strpos($script, 'window.location.assign(explicitReturnTarget)');

        $this->assertIsInt($historyBackPosition);
        $this->assertIsInt($explicitReturnPosition);
        $this->assertLessThan($explicitReturnPosition, $historyBackPosition);
    }
}
