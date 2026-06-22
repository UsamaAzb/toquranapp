<?php

namespace Tests\Feature\CoreLms;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\BrowserPush\BrowserPushService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BrowserPushNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createPushSubscriptionsTable();
    }

    public function test_service_worker_is_push_only_and_does_not_register_fetch_handler(): void
    {
        $response = $this->get('/service-worker.js');

        $response->assertOk();
        $response->assertHeader('Service-Worker-Allowed', '/');

        $script = $response->getContent();
        $this->assertStringContainsString("addEventListener('push'", $script);
        $this->assertStringContainsString("addEventListener('notificationclick'", $script);
        $this->assertStringContainsString('/pwa/icons/toquran-notification-badge.png', $script);
        $this->assertFileExists(public_path('pwa/icons/toquran-notification-badge.png'));
        $this->assertStringNotContainsString("addEventListener('fetch'", $script);
    }

    public function test_authenticated_user_can_store_and_revoke_browser_push_subscription(): void
    {
        $user = User::factory()->create();

        $payload = $this->subscriptionPayload();

        $this->actingAs($user)
            ->postJson(route('browser-push.subscriptions.store'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'saved']);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint_hash' => PushSubscription::hashEndpoint($payload['endpoint']),
            'revoked_at' => null,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('browser-push.subscriptions.destroy'), [
                'endpoint' => $payload['endpoint'],
            ])
            ->assertOk()
            ->assertJson(['status' => 'revoked']);

        $this->assertNotNull(PushSubscription::query()
            ->where('endpoint_hash', PushSubscription::hashEndpoint($payload['endpoint']))
            ->value('revoked_at'));
    }

    public function test_guest_cannot_store_browser_push_subscription(): void
    {
        $this->postJson(route('browser-push.subscriptions.store'), $this->subscriptionPayload())
            ->assertUnauthorized();
    }

    public function test_browser_push_kill_switch_skips_delivery_even_with_saved_subscription(): void
    {
        config([
            'browser-push.enabled' => false,
            'browser-push.vapid.public_key' => 'public-key',
            'browser-push.vapid.private_key' => 'private-key',
            'browser-push.vapid.subject' => 'mailto:support@toquran.org',
        ]);

        $user = User::factory()->create();
        app(BrowserPushService::class)->storeSubscription($user, $this->subscriptionPayload(), 'Feature test');

        $result = app(BrowserPushService::class)->sendToUser($user, [
            'title' => 'Test',
            'body' => 'Should not send',
            'url' => '/students',
        ]);

        $this->assertSame(['sent' => 0, 'failed' => 0, 'skipped' => 1, 'expired' => 0], $result);
    }

    /**
     * @return array{endpoint:string,keys:array{p256dh:string,auth:string},contentEncoding:string}
     */
    private function subscriptionPayload(): array
    {
        return [
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/test-endpoint',
            'keys' => [
                'p256dh' => str_repeat('a', 88),
                'auth' => str_repeat('b', 24),
            ],
            'contentEncoding' => 'aes128gcm',
        ];
    }

    private function createPushSubscriptionsTable(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('public_key', 512);
            $table->string('auth_token', 512);
            $table->string('content_encoding', 32)->default('aes128gcm');
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'revoked_at']);
        });
    }
}
