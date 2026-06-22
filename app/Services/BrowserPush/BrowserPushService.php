<?php

namespace App\Services\BrowserPush;

use App\Models\PushSubscription;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class BrowserPushService
{
    public function isConfigured(): bool
    {
        return filled(config('browser-push.vapid.public_key'))
            && filled(config('browser-push.vapid.private_key'))
            && filled(config('browser-push.vapid.subject'));
    }

    public function isSendingEnabled(): bool
    {
        return (bool) config('browser-push.enabled') && $this->isConfigured();
    }

    /**
     * @param array{endpoint:string,keys:array{p256dh:string,auth:string},contentEncoding?:string} $subscription
     */
    public function storeSubscription(User $user, array $subscription, ?string $userAgent = null): PushSubscription
    {
        $endpoint = (string) $subscription['endpoint'];
        $keys = $subscription['keys'];

        return PushSubscription::query()->updateOrCreate(
            ['endpoint_hash' => PushSubscription::hashEndpoint($endpoint)],
            [
                'user_id' => $user->id,
                'endpoint' => $endpoint,
                'public_key' => (string) $keys['p256dh'],
                'auth_token' => (string) $keys['auth'],
                'content_encoding' => (string) ($subscription['contentEncoding'] ?? 'aes128gcm'),
                'user_agent' => $userAgent,
                'last_seen_at' => now(config('app.timezone')),
                'revoked_at' => null,
            ]
        );
    }

    public function revokeSubscription(User $user, ?string $endpoint = null): int
    {
        $query = PushSubscription::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at');

        if ($endpoint !== null && $endpoint !== '') {
            $query->where('endpoint_hash', PushSubscription::hashEndpoint($endpoint));
        }

        return $query->update([
            'revoked_at' => now(config('app.timezone')),
            'updated_at' => now(config('app.timezone')),
        ]);
    }

    /**
     * @param array{title:string,body:string,url:string,tag?:string} $message
     * @return array{sent:int,failed:int,skipped:int,expired:int}
     */
    public function sendToUser(int|User $user, array $message): array
    {
        if (! $this->isSendingEnabled()) {
            return ['sent' => 0, 'failed' => 0, 'skipped' => 1, 'expired' => 0];
        }

        $userId = $user instanceof User ? (int) $user->id : (int) $user;

        $subscriptions = PushSubscription::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get();

        if ($subscriptions->isEmpty()) {
            return ['sent' => 0, 'failed' => 0, 'skipped' => 1, 'expired' => 0];
        }

        $payload = json_encode([
            'title' => $message['title'],
            'body' => $message['body'],
            'url' => $this->sameOriginPath((string) $message['url']),
            'tag' => $message['tag'] ?? null,
            'icon' => asset('pwa/icons/toquran-icon-192.png').'?v=20260623-icon-padding',
            'badge' => asset('pwa/icons/toquran-notification-badge.png').'?v=20260623-icon-padding',
        ], JSON_THROW_ON_ERROR);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => (string) config('browser-push.vapid.subject'),
                'publicKey' => (string) config('browser-push.vapid.public_key'),
                'privateKey' => (string) config('browser-push.vapid.private_key'),
            ],
        ], [
            'TTL' => (int) config('browser-push.ttl', 3600),
            'urgency' => (string) config('browser-push.urgency', 'normal'),
        ], 10);

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                ]),
                $payload
            );
        }

        $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'expired' => 0];

        try {
            foreach ($webPush->flush() as $report) {
                $endpointHash = PushSubscription::hashEndpoint($report->getEndpoint());

                if ($report->isSuccess()) {
                    $result['sent']++;
                    PushSubscription::query()
                        ->where('endpoint_hash', $endpointHash)
                        ->update([
                            'last_seen_at' => now(config('app.timezone')),
                            'updated_at' => now(config('app.timezone')),
                        ]);

                    continue;
                }

                $result['failed']++;

                if ($report->isSubscriptionExpired()) {
                    $result['expired']++;
                    PushSubscription::query()
                        ->where('endpoint_hash', $endpointHash)
                        ->update([
                            'revoked_at' => now(config('app.timezone')),
                            'updated_at' => now(config('app.timezone')),
                        ]);
                }

                Log::warning('Browser push delivery failed.', [
                    'endpoint_hash' => $endpointHash,
                    'reason' => $report->getReason(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Browser push flush failed.', ['exception' => $exception]);
            $result['failed'] += $subscriptions->count();
        }

        return $result;
    }

    public function notifyTaskSubmittedForReview(int $studentId, int $sessionTaskId): void
    {
        $student = Student::query()
            ->with('parent.user')
            ->find($studentId);

        $parentUser = $student?->parent?->user;

        if (! $student || ! $parentUser) {
            return;
        }

        $key = "browser-push:task-review:{$parentUser->id}:{$studentId}:{$sessionTaskId}";
        if (! Cache::add($key, true, now(config('app.timezone'))->addMinutes((int) config('browser-push.dedupe_minutes', 10)))) {
            return;
        }

        $this->safeSend($parentUser, [
            'title' => 'Task ready for review',
            'body' => $student->display_name.' submitted work for review.',
            'url' => route('parent.task-approvals', ['student' => $student->id], false),
            'tag' => 'task-review-'.$student->id,
        ]);
    }

    /**
     * @param array<int> $giftIds
     */
    public function notifyReachedGifts(array $giftIds): void
    {
        if ($giftIds === []) {
            return;
        }

        $gifts = StudentGift::query()
            ->with(['student.user', 'student.parent.user'])
            ->whereIn('id', array_values(array_unique(array_map('intval', $giftIds))))
            ->get();

        foreach ($gifts as $gift) {
            $student = $gift->student;
            if (! $student) {
                continue;
            }

            $title = (string) ($gift->gift_name ?? 'gift');
            $body = $student->display_name.' reached '.$title.'.';

            $this->notifyGiftRecipient(
                $gift,
                $student->parent?->user,
                'Gift reached',
                $body,
                route('parent.reward-discpline', ['student_id' => $student->id], false)
            );

            if ($student->user) {
                $this->notifyGiftRecipient(
                    $gift,
                    $student->user,
                    'You reached a gift',
                    'You reached '.$title.'.',
                    route('student.journey.board', ['student_id' => $student->id], false)
                );
            }
        }
    }

    /**
     * @param array{title:string,body:string,url:string,tag?:string} $message
     */
    private function safeSend(User $user, array $message): void
    {
        try {
            $this->sendToUser($user, $message);
        } catch (Throwable $exception) {
            Log::error('Browser push notification failed.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);
        }
    }

    private function notifyGiftRecipient(StudentGift $gift, ?User $user, string $title, string $body, string $url): void
    {
        if (! $user) {
            return;
        }

        $key = "browser-push:gift-reached:{$gift->id}:{$user->id}";
        if (! Cache::add($key, true, now(config('app.timezone'))->addDays(14))) {
            return;
        }

        $this->safeSend($user, [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'tag' => 'gift-reached-'.$gift->id,
        ]);
    }

    private function sameOriginPath(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path);

            return (string) ($parsed['path'] ?? '/');
        }

        return str_starts_with($path, '/') ? $path : '/';
    }
}
