<?php

return [
    'dictionary' => [
        'host' => env('VOCABULARY_DICTIONARY_HOST', 'dictionary.cambridge.org'),
        'base_url' => env('VOCABULARY_DICTIONARY_BASE_URL', 'https://dictionary.cambridge.org'),
        'timeout_seconds' => 10,
        'max_bytes' => 2 * 1024 * 1024,
        'allowed_url_extensions' => [
            'mp3',
            'mpeg',
            'mpga',
            'ogg',
            'oga',
            'wav',
            'm4a',
            'aac',
        ],
    ],

    'audio' => [
        'primary_us_path' => 'camb_words_api/us_sounds',
        'dictionary_us_path' => 'dictionary_sounds/us_sounds',
        'owner_recording_path' => 'camb_words_api/pcrecord',
        'extension' => 'mp3',
    ],

    'images' => [
        'word_image_path' => 'camb_words_api/images',
        'max_bytes' => 2 * 1024 * 1024,
    ],

    'games' => [
        'enabled' => env('VOCABULARY_GAMES_ENABLED', false),
        'progress_dot_cap' => 10,
        'distractor_default' => 6,
        'distractor_cap' => 9,
        'all_caps_words' => [
            'DIY',
            'FAQ',
            'NASA',
            'OK',
            'TV',
            'UK',
            'USA',
        ],
        'difficulty_labels' => [
            'sprout' => 'Sprout',
            'climber' => 'Climber',
            'champion' => 'Champion',
        ],
        'hangman' => [
            'mistakes' => 6,
            'champion_seconds_per_letter' => 6,
            'sprout_hints_short_word' => 1,
            'sprout_hints_long_word' => 2,
            'climber_hints_short_word' => 0,
            'climber_hints_long_word' => 1,
            'long_word_letters' => 6,
        ],
        'timed_choices' => [
            'sprout' => 10,
            'climber' => 7,
            'champion' => 4,
        ],
        'random_source_word_limit' => 15,
        'custom_source_word_limit' => 30,
    ],

    'wrong_options' => [
        'confusions' => [
            ['c', 'k'],
            ['s', 'c'],
            ['f', 'ph'],
            ['a', 'e', 'i', 'o', 'u'],
        ],
        'max_attempts' => 40,
    ],

    'free_play' => [
        'min_length' => 1,
        'max_length' => 40,
        'allowed_pattern' => "/\\A[a-zA-Z][a-zA-Z '\\-]*\\z/",
    ],
];
