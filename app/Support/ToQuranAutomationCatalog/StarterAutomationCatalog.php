<?php

namespace App\Support\ToQuranAutomationCatalog;

use InvalidArgumentException;

final class StarterAutomationCatalog
{
    public const VERSION = '2026-06-15-wellbeing-mdj-v1';

    public function entries(): array
    {
        return [
            ...$this->wellBeingVersionedRoutines(),
            ...$this->myDeenJourneyVersionedRoutines(),
            ...$this->myDeenJourneySeriesTasks(),
        ];
    }

    private function wellBeingVersionedRoutines(): array
    {
        return [
            $this->versionedRoutine(
                'wb-personal-hygiene',
                'Well Being',
                'Personal Hygiene',
                'Visible personal hygiene routine for parent-confirmed habit building.',
                'Personal Hygiene',
                [
                    ['key' => 'brush_once', 'name' => 'Brush Teeth Once', 'text' => 'Brush your teeth once today. Mark done when finished.'],
                    ['key' => 'morning_night', 'name' => 'Morning And Night', 'text' => 'Brush your teeth in the morning and at night. Mark done when finished.'],
                    ['key' => 'clean_ready', 'name' => 'Clean And Ready', 'text' => 'Brush your teeth, wash your face, and look clean and ready.'],
                    ['key' => 'full_routine', 'name' => 'Full Hygiene Routine', 'text' => 'Complete your morning and bedtime hygiene routine as agreed with your family.'],
                ],
            ),
            $this->versionedRoutine(
                'wb-room-reset',
                'Well Being',
                'Room Reset',
                'Visible room or study-space responsibility.',
                'Room Reset',
                [
                    ['key' => 'sleep_area', 'name' => 'Sleeping Area', 'text' => 'Make your bed or straighten your sleeping area.'],
                    ['key' => 'bed_floor', 'name' => 'Bed And Floor', 'text' => 'Make your bed and clear the floor.'],
                    ['key' => 'bed_desk_bag', 'name' => 'Bed, Desk, And Bag', 'text' => 'Reset your bed, desk, and learning bag or books.'],
                    ['key' => 'weekly_standard', 'name' => 'Weekly Standard', 'text' => 'Keep your room or study area acceptable this week.'],
                ],
                recurrenceKind: 'weekly',
            ),
            $this->versionedRoutine(
                'wb-sleep-routine',
                'Well Being',
                'Sleep Routine',
                'Bedtime readiness and sleep-routine support.',
                'Sleep Routine',
                [
                    ['key' => 'start_on_time', 'name' => 'Start On Time', 'text' => 'Start your bedtime routine on time today.'],
                    ['key' => 'screen_away', 'name' => 'Screen Away', 'text' => 'Put screens away before bedtime as agreed with your family.'],
                    ['key' => 'calm_prep', 'name' => 'Calm Preparation', 'text' => 'Prepare for sleep calmly and on time.'],
                    ['key' => 'weekly_sleep', 'name' => 'Weekly Sleep Routine', 'text' => 'Keep your sleep routine better this week.'],
                ],
            ),
            $this->versionedRoutine(
                'wb-learning-readiness',
                'Well Being',
                'Learning Readiness',
                'Preparation for Quran, Arabic, and Islamic learning sessions.',
                'Learning Readiness',
                [
                    ['key' => 'materials', 'name' => 'Materials Ready', 'text' => 'Prepare the materials you need for your next lesson.'],
                    ['key' => 'bag_books', 'name' => 'Bag And Books', 'text' => 'Prepare your bag, books, or learning materials before class.'],
                    ['key' => 'without_reminders', 'name' => 'Without Reminders', 'text' => 'Prepare your materials without waiting for many reminders.'],
                    ['key' => 'weekly_ready', 'name' => 'Weekly Readiness', 'text' => 'Stay ready for your lessons this week.'],
                ],
                recurrenceKind: 'weekly',
            ),
            $this->versionedRoutine(
                'wb-home-responsibility',
                'Well Being',
                'Home Responsibility',
                'One clear family responsibility selected by the parent.',
                'Home Responsibility',
                [
                    ['key' => 'assigned_job', 'name' => 'Assigned Home Job', 'text' => 'Complete your assigned home job today.'],
                    ['key' => 'without_arguing', 'name' => 'Without Arguing', 'text' => 'Complete your assigned home job without arguing.'],
                    ['key' => 'without_reminders', 'name' => 'Without Reminders', 'text' => 'Complete your assigned home job without waiting for many reminders.'],
                    ['key' => 'weekly_responsibility', 'name' => 'Weekly Responsibility', 'text' => 'Keep your weekly family responsibility.'],
                ],
                recurrenceKind: 'weekly',
            ),
            $this->versionedRoutine(
                'wb-screen-balance',
                'Well Being',
                'Screen Balance',
                'Parent-defined screen balance agreement.',
                'Screen Balance',
                [
                    ['key' => 'follow_rule', 'name' => 'Follow Screen Rule', 'text' => 'Follow today\'s screen-time rule.'],
                    ['key' => 'stop_when_asked', 'name' => 'Stop When Asked', 'text' => 'Stop screen time when your parent asks.'],
                    ['key' => 'responsibilities_first', 'name' => 'Responsibilities First', 'text' => 'Finish your responsibilities before screen time.'],
                    ['key' => 'weekly_agreement', 'name' => 'Weekly Agreement', 'text' => 'Keep your weekly screen agreement.'],
                ],
            ),
        ];
    }

    private function myDeenJourneyVersionedRoutines(): array
    {
        return [
            $this->versionedRoutine(
                'mdj-salah',
                'My Deen Journey',
                'Salah',
                'Separate salah tasks with readiness and quality versions. These are habit-building targets selected by parent or teacher, not a ruling about religious obligation.',
                'Salah',
                [
                    ['key' => 'readiness', 'name' => 'Readiness'],
                    ['key' => 'completed', 'name' => 'Completed'],
                    ['key' => 'on_time', 'name' => 'On Time'],
                    ['key' => 'calm_focus', 'name' => 'Calm And Focused'],
                    ['key' => 'after_salah_adhkar', 'name' => 'With After-Salah Adhkar'],
                ],
                tasks: collect(['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'])
                    ->map(fn (string $salah, int $index): array => [
                        'key' => strtolower($salah),
                        'title' => $salah,
                        'sort_order' => $index + 1,
                        'description' => "Work on your {$salah} practice target.",
                        'version_descriptions' => [
                            'readiness' => "Prepare for {$salah} when it is part of your current practice target.",
                            'completed' => "Complete {$salah} when it is part of your current practice target.",
                            'on_time' => "Complete {$salah} with stronger attention to timing when it is part of your current practice target.",
                            'calm_focus' => "Complete {$salah} calmly without rushing when it is part of your current practice target.",
                            'after_salah_adhkar' => "Complete {$salah}, then practise the selected after-salah adhkar assigned by your teacher or parent.",
                        ],
                    ])
                    ->all(),
            ),
            $this->versionedRoutine(
                'mdj-wudu',
                'My Deen Journey',
                'Wudu',
                'Wudu practice, independence, and calmness.',
                'Wudu',
                [
                    ['key' => 'with_support', 'name' => 'With Support', 'text' => 'Practise wudu steps with support.'],
                    ['key' => 'before_salah', 'name' => 'Before Salah', 'text' => 'Make wudu before your assigned salah target.'],
                    ['key' => 'independent', 'name' => 'Independent', 'text' => 'Make wudu independently.'],
                    ['key' => 'calm', 'name' => 'Calm Wudu', 'text' => 'Make wudu calmly without rushing.'],
                ],
            ),
            $this->versionedRoutine(
                'mdj-quran',
                'My Deen Journey',
                'Quran',
                'Generic Quran habit tasks that do not depend on Quran API integration.',
                'Quran',
                [
                    ['key' => 'listen', 'name' => 'Listening', 'text' => 'Listen carefully to your assigned Quran portion.'],
                    ['key' => 'repeat', 'name' => 'Repeat After Recitation', 'text' => 'Repeat after your assigned Quran recitation.'],
                    ['key' => 'read_support', 'name' => 'Read With Support', 'text' => 'Read your assigned Quran portion with support.'],
                    ['key' => 'reading', 'name' => 'Today\'s Reading', 'text' => 'Complete today\'s assigned Quran reading.'],
                    ['key' => 'reflection', 'name' => 'Reflection', 'text' => 'Write or say one word, reminder, or lesson from your Quran task.'],
                ],
            ),
            $this->versionedRoutine(
                'mdj-dua-practice',
                'My Deen Journey',
                'Dua Practice',
                'Versioned dua practice; reviewed Arabic/source text can be added to descriptions later.',
                'Dua Practice',
                [
                    ['key' => 'before_eating', 'name' => 'Before Eating', 'text' => 'Practise the assigned before-eating dua.'],
                    ['key' => 'before_sleep', 'name' => 'Before Sleep', 'text' => 'Practise the assigned before-sleep dua in addition to the before-eating dua.'],
                    ['key' => 'waking_up', 'name' => 'Waking Up', 'text' => 'Practise the assigned waking-up dua along with your earlier duas.'],
                    ['key' => 'leaving_home', 'name' => 'Leaving Home', 'text' => 'Practise the assigned leaving-home dua along with your earlier duas.'],
                    ['key' => 'quranic_dua', 'name' => 'Quranic Dua', 'text' => 'Practise one assigned Quranic dua and say what it asks Allah for.'],
                ],
            ),
            $this->versionedRoutine(
                'mdj-morning-adhkar',
                'My Deen Journey',
                'Morning Adhkar',
                'Morning adhkar set that grows by version.',
                'Morning Adhkar',
                [
                    ['key' => 'one', 'name' => 'One Item', 'text' => 'Complete 1 assigned morning dhikr or dua.'],
                    ['key' => 'two', 'name' => 'Two Items', 'text' => 'Complete 2 assigned morning adhkar.'],
                    ['key' => 'three', 'name' => 'Three Items', 'text' => 'Complete 3 assigned morning adhkar.'],
                    ['key' => 'set', 'name' => 'Morning Set', 'text' => 'Complete your assigned morning adhkar set.'],
                    ['key' => 'focus', 'name' => 'With Focus', 'text' => 'Complete your morning adhkar with focus and understanding.'],
                ],
            ),
            $this->versionedRoutine(
                'mdj-evening-adhkar',
                'My Deen Journey',
                'Evening Adhkar',
                'Evening or bedtime adhkar set that grows by version.',
                'Evening Adhkar',
                [
                    ['key' => 'one', 'name' => 'One Item', 'text' => 'Complete 1 assigned evening or bedtime dhikr or dua.'],
                    ['key' => 'two', 'name' => 'Two Items', 'text' => 'Complete 2 assigned evening or bedtime adhkar.'],
                    ['key' => 'three', 'name' => 'Three Items', 'text' => 'Complete 3 assigned evening or bedtime adhkar.'],
                    ['key' => 'set', 'name' => 'Evening Set', 'text' => 'Complete your assigned evening or bedtime adhkar set.'],
                    ['key' => 'calm', 'name' => 'Calm Before Sleep', 'text' => 'Complete your evening or bedtime adhkar calmly before sleep.'],
                ],
            ),
            $this->versionedRoutine(
                'mdj-weekly-reflection',
                'My Deen Journey',
                'Deen Reflection',
                'Weekly reflection without turning character into fake daily checkboxes.',
                'Deen Reflection',
                [
                    ['key' => 'practised', 'name' => 'Practised This Week', 'text' => 'What did you practise this week from your Islamic learning?'],
                    ['key' => 'good_action', 'name' => 'One Good Action', 'text' => 'Choose one good action you did this week and explain it briefly.'],
                    ['key' => 'improve', 'name' => 'One Thing To Improve', 'text' => 'What is one Deen habit you want to improve next week?'],
                ],
                recurrenceKind: 'weekly',
            ),
        ];
    }

    private function myDeenJourneySeriesTasks(): array
    {
        return [
            [
                'type' => 'series_task',
                'catalog_key' => 'mdj-dua-bank-series',
                'subject_title' => 'My Deen Journey',
                'title' => 'Dua Bank',
                'description' => 'One dua at a time from a reviewed Shared Library source folder.',
                'status' => 'draft',
                'task_type' => 'Assignment',
                'default_points' => 5,
                'max_points' => 10,
                'recurrence' => ['kind' => 'daily', 'interval' => 1],
                'sequence_behavior' => 'stop_at_end',
                'release_policy' => 'wait_for_completion',
                'library_folder_path' => ['My Deen Journey', 'Dua Bank'],
                'versions' => [
                    ['key' => 'starter', 'display_name' => 'Starter Duas', 'description' => 'Short daily-life duas reviewed for launch use.'],
                ],
            ],
        ];
    }

    private function versionedRoutine(
        string $catalogKey,
        string $subjectTitle,
        string $title,
        string $description,
        string $taskTitle,
        array $versions,
        string $recurrenceKind = 'daily',
        ?array $tasks = null
    ): array {
        if ($versions === []) {
            throw new InvalidArgumentException("Versioned routine '{$catalogKey}' requires at least one version.");
        }

        $tasks ??= [[
            'key' => 'main',
            'title' => $taskTitle,
            'sort_order' => 1,
            'description' => $versions[0]['text'] ?? null,
            'version_descriptions' => collect($versions)
                ->mapWithKeys(fn (array $version): array => [$version['key'] => $version['text']])
                ->all(),
        ]];

        return [
            'type' => 'versioned_routine',
            'catalog_key' => $catalogKey,
            'subject_title' => $subjectTitle,
            'title' => $title,
            'description' => $description,
            'status' => 'draft',
            'task_type' => 'Assignment',
            'default_points' => 5,
            'max_points' => 10,
            'recurrence' => ['kind' => $recurrenceKind, 'interval' => 1],
            'versions' => array_map(
                fn (array $version): array => [
                    'key' => $version['key'],
                    'display_name' => $version['name'] ?? $version['display_name'],
                ],
                $versions
            ),
            'tasks' => array_values($tasks),
        ];
    }
}
