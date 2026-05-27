<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaInstallabilityTest extends TestCase
{
    public function test_manifest_is_public_and_installable(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/manifest+json; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'max-age=86400, public');

        $manifest = $response->json();

        $this->assertSame('To Quran LMS', $manifest['name']);
        $this->assertSame('To Quran', $manifest['short_name']);
        $this->assertSame('/login', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertFalse($manifest['prefer_related_applications']);
        $this->assertContains('192x192', array_column($manifest['icons'], 'sizes'));
        $this->assertContains('512x512', array_column($manifest['icons'], 'sizes'));
        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));
    }

    public function test_login_shell_includes_pwa_metadata_without_custom_prompt_script(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('apple-mobile-web-app-capable', false);
        $response->assertSee('apple-mobile-web-app-title', false);
        $response->assertSee('toquran-apple-touch-icon.png', false);
        $response->assertSee('assets/img/favicon/favicon.ico', false);
        $response->assertDontSee('beforeinstallprompt', false);
        $response->assertDontSee('__toquranInstallPromptInitialized', false);
    }

    public function test_shared_pwa_meta_partial_keeps_expected_ios_and_manifest_hooks(): void
    {
        $meta = view('pwa.meta')->render();

        $this->assertStringContainsString('rel="manifest"', $meta);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $meta);
        $this->assertStringContainsString('apple-mobile-web-app-status-bar-style', $meta);
        $this->assertStringContainsString('apple-mobile-web-app-title', $meta);
        $this->assertStringContainsString('apple-touch-icon', $meta);
    }
}
