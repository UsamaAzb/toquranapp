<?php

return [
    'enabled' => (bool) env('BROWSER_PUSH_ENABLED', false),

    'vapid' => [
        'subject' => env('WEBPUSH_VAPID_SUBJECT', 'mailto:support@toquran.org'),
        'public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
        'private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
    ],

    'ttl' => (int) env('WEBPUSH_TTL', 3600),
    'urgency' => env('WEBPUSH_URGENCY', 'normal'),
    'dedupe_minutes' => (int) env('WEBPUSH_DEDUPE_MINUTES', 10),
];
