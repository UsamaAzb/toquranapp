<?php

namespace App\Http\Controllers;

use App\Services\BrowserPush\BrowserPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrowserPushSubscriptionController extends Controller
{
    public function config(BrowserPushService $push): JsonResponse
    {
        return response()->json([
            'supported' => true,
            'configured' => $push->isConfigured(),
            'sending_enabled' => $push->isSendingEnabled(),
            'public_key' => config('browser-push.vapid.public_key'),
        ]);
    }

    public function store(Request $request, BrowserPushService $push): JsonResponse
    {
        $payload = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:512'],
            'keys.auth' => ['required', 'string', 'max:512'],
            'contentEncoding' => ['nullable', 'string', 'max:32'],
        ]);

        $push->storeSubscription(
            $request->user(),
            $payload,
            substr((string) $request->userAgent(), 0, 512) ?: null
        );

        return response()->json(['status' => 'saved']);
    }

    public function destroy(Request $request, BrowserPushService $push): JsonResponse
    {
        $payload = $request->validate([
            'endpoint' => ['nullable', 'url', 'max:2048'],
        ]);

        $revoked = $push->revokeSubscription($request->user(), $payload['endpoint'] ?? null);

        return response()->json(['status' => 'revoked', 'revoked' => $revoked]);
    }

    public function test(Request $request, BrowserPushService $push): JsonResponse
    {
        $result = $push->sendToUser($request->user(), [
            'title' => 'To Quran notifications are ready',
            'body' => 'This device can receive To Quran updates.',
            'url' => $request->user()?->hasRole('parent')
                ? route('parent.students', [], false)
                : route('student.workplace', [], false),
            'tag' => 'browser-push-test',
        ]);

        return response()->json(['status' => 'queued', 'result' => $result]);
    }
}
