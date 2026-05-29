<?php

return [
    'parent_behavior_subject_id' => (int) env('TOQURAN_PARENT_BEHAVIOR_SUBJECT_ID', 16),
    'default_teacher_email' => env('TOQURAN_DEFAULT_TEACHER_EMAIL', 'drosamaqandil@gmail.com'),
    'legacy_library_owner_user_ids' => array_values(array_filter(
        array_map(
            static fn ($id) => (int) trim($id),
            explode(',', (string) env('LEGACY_LIBRARY_OWNER_USER_IDS', ''))
        ),
        static fn ($id) => $id > 0
    )),
];
