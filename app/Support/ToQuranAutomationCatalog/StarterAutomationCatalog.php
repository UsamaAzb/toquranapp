<?php

namespace App\Support\ToQuranAutomationCatalog;

use InvalidArgumentException;

final class StarterAutomationCatalog
{
    public const VERSION = '2026-06-17-wellbeing-mdj-v4';

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
                    ['key' => 'brush_once', 'name' => 'Brush Teeth Once', 'task_keys' => ['brush_once']],
                    ['key' => 'morning_and_night', 'name' => 'Brush Morning And Night', 'task_keys' => ['brush_morning', 'brush_night']],
                    ['key' => 'clean_and_ready', 'name' => 'Clean And Ready', 'task_keys' => ['brush_morning', 'brush_night', 'wash_face']],
                    ['key' => 'morning_bedtime', 'name' => 'Morning And Bedtime Hygiene Routine', 'task_keys' => ['brush_morning', 'brush_night', 'wash_face', 'clean_clothes']],
                ],
                tasks: [
                    $this->task('brush_once', 'Brush Teeth Once', 'Brush your teeth once today. Mark done when you finish.', 1),
                    $this->task('brush_morning', 'Brush Teeth In The Morning', 'Brush your teeth in the morning. Mark done when you finish.', 1),
                    $this->task('brush_night', 'Brush Teeth At Night', 'Brush your teeth at night before sleeping. Mark done when you finish.', 2),
                    $this->task('wash_face', 'Wash Face', 'Wash your face and get fresh for the day.', 3),
                    $this->task('clean_clothes', 'Prepare Clean Clothes', 'Wear clean clothes or prepare clean clothes with your parent.', 4),
                ],
            ),
            $this->versionedRoutine(
                'wb-room-reset',
                'Well Being',
                'Room Reset',
                'Visible room or study-space responsibility.',
                'Room Reset',
                [
                    ['key' => 'bed_area', 'name' => 'Make Bed Or Sleeping Area', 'task_keys' => ['make_bed']],
                    ['key' => 'bed_and_floor', 'name' => 'Bed And Floor Reset', 'task_keys' => ['make_bed', 'clear_floor']],
                    ['key' => 'bed_desk_bag', 'name' => 'Bed, Desk, And Bag Reset', 'task_keys' => ['make_bed', 'clear_floor', 'reset_desk', 'prepare_learning_bag']],
                    ['key' => 'weekly_room_standard', 'name' => 'Weekly Room Standard', 'task_keys' => ['make_bed', 'clear_floor', 'reset_desk', 'prepare_learning_bag']],
                ],
                tasks: [
                    $this->task('make_bed', 'Make Your Bed', 'Make your bed or straighten your sleeping area.', 1),
                    $this->task('clear_floor', 'Clear The Floor', 'Pick up things from the floor and put them where they belong.', 2),
                    $this->task('reset_desk', 'Reset Your Desk', 'Make your desk or study place neat enough to learn.', 3),
                    $this->task('prepare_learning_bag', 'Prepare Learning Bag Or Books', 'Put your learning bag, books, or Quran materials in the right place.', 4),
                ],
            ),
            $this->versionedRoutine(
                'wb-sleep-routine',
                'Well Being',
                'Sleep Routine',
                'Bedtime readiness and sleep-routine support.',
                'Sleep Routine',
                [
                    ['key' => 'start_bedtime', 'name' => 'Start Bedtime Routine', 'task_keys' => ['start_bedtime']],
                    ['key' => 'screen_away', 'name' => 'Screen Away Before Sleep', 'task_keys' => ['start_bedtime', 'screens_away']],
                    ['key' => 'calm_sleep', 'name' => 'Calm Sleep Preparation', 'task_keys' => ['start_bedtime', 'screens_away', 'calm_down']],
                    ['key' => 'weekly_sleep', 'name' => 'Weekly Sleep Routine', 'task_keys' => ['start_bedtime', 'screens_away', 'prepare_tomorrow', 'calm_down']],
                ],
                tasks: [
                    $this->task('start_bedtime', 'Start Bedtime On Time', 'Start getting ready for sleep at the time agreed with your family.', 1),
                    $this->task('screens_away', 'Put Screens Away', 'Put screens away in the agreed place before bedtime.', 2),
                    $this->task('prepare_tomorrow', 'Prepare For Tomorrow', 'Prepare the clothes, books, or things you need for tomorrow.', 3),
                    $this->task('calm_down', 'Calm Down Before Sleep', 'Do your calm bedtime step, such as reading quietly, making dua, or relaxing without screens.', 4),
                ],
            ),
            $this->versionedRoutine(
                'wb-learning-readiness',
                'Well Being',
                'Learning Readiness',
                'Preparation for Quran, Arabic, and Islamic learning sessions.',
                'Learning Readiness',
                [
                    ['key' => 'tutoring_materials', 'name' => 'Prepare Tutoring Materials', 'task_keys' => ['prepare_materials']],
                    ['key' => 'bag_books', 'name' => 'Prepare Bag Or Books', 'task_keys' => ['prepare_materials', 'prepare_learning_bag']],
                    ['key' => 'without_reminders', 'name' => 'Prepare Without Reminders', 'task_keys' => ['prepare_materials', 'prepare_learning_bag', 'ask_help_early']],
                    ['key' => 'weekly_readiness', 'name' => 'Weekly Learning Readiness', 'task_keys' => ['prepare_materials', 'prepare_learning_bag', 'join_on_time', 'good_learning_place']],
                ],
                tasks: [
                    $this->task('prepare_materials', 'Prepare Quran Or Lesson Materials', 'Prepare the Quran, book, notebook, pencil, or device you need for your lesson.', 1),
                    $this->task('prepare_learning_bag', 'Prepare Bag Or Books', 'Prepare your bag, books, or learning materials before class.', 2),
                    $this->task('ask_help_early', 'Ask For Help Early', 'If something is missing or not working, ask your parent or teacher before the lesson starts.', 3),
                    $this->task('join_on_time', 'Join On Time', 'Be ready to join your lesson on time.', 4),
                    $this->task('good_learning_place', 'Sit In A Good Learning Place', 'Sit in a quiet and suitable place for learning.', 5),
                ],
            ),
            $this->versionedRoutine(
                'wb-home-responsibility',
                'Well Being',
                'Home Responsibility',
                'One clear family responsibility selected by the parent.',
                'Home Responsibility',
                [
                    ['key' => 'assigned_job', 'name' => 'Assigned Home Job', 'task_keys' => ['one_family_job']],
                    ['key' => 'without_arguing', 'name' => 'Home Job Without Arguing', 'task_keys' => ['one_family_job', 'do_it_kindly']],
                    ['key' => 'without_reminders', 'name' => 'Home Job Without Reminders', 'task_keys' => ['one_family_job', 'do_it_kindly', 'without_many_reminders']],
                    ['key' => 'weekly_responsibility', 'name' => 'Weekly Family Responsibility', 'task_keys' => ['one_family_job', 'do_it_kindly', 'without_many_reminders', 'extra_help']],
                ],
                tasks: [
                    $this->task('one_family_job', 'Do One Family Job', 'Do the family job your parent chose for you today.', 1),
                    $this->task('do_it_kindly', 'Do It Kindly', 'Do your family job with kind words and no arguing.', 2),
                    $this->task('without_many_reminders', 'Do It Without Many Reminders', 'Start or finish your family job without waiting for many reminders.', 3),
                    $this->task('extra_help', 'Help Extra For Two Minutes', 'Give two extra minutes of help at home when your parent asks.', 4),
                ],
            ),
            $this->versionedRoutine(
                'wb-screen-balance',
                'Well Being',
                'Screen Balance',
                'Parent-defined screen balance agreement.',
                'Screen Balance',
                [
                    ['key' => 'follow_rule', 'name' => 'Follow Screen Rule', 'task_keys' => ['follow_screen_rule']],
                    ['key' => 'stop_when_asked', 'name' => 'Stop When Asked', 'task_keys' => ['follow_screen_rule', 'stop_when_parent_says']],
                    ['key' => 'responsibilities_first', 'name' => 'Responsibilities Before Screen', 'task_keys' => ['responsibilities_first', 'stop_when_parent_says']],
                    ['key' => 'weekly_agreement', 'name' => 'Weekly Screen Agreement', 'task_keys' => ['responsibilities_first', 'stop_when_parent_says', 'device_agreed_place', 'non_screen_activity']],
                ],
                tasks: [
                    $this->task('follow_screen_rule', 'Follow Screen Rule', 'Follow the screen-time rule your parent set for today.', 1),
                    $this->task('responsibilities_first', 'Finish Responsibilities Before Screen Time', 'Finish your agreed responsibility before screen time starts.', 2),
                    $this->task('stop_when_parent_says', 'Stop When Parent Says Screen Time Is Finished', 'When your parent says screen time is finished, stop and put the screen down.', 3),
                    $this->task('device_agreed_place', 'Put Device In The Agreed Place', 'Put the device in the place agreed with your family.', 4),
                    $this->task('non_screen_activity', 'Choose One Non-Screen Activity', 'Choose one non-screen activity today, such as reading, playing, drawing, or helping at home.', 5),
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
                'Separate salah habit-building targets selected by parent or teacher; not a ruling about religious obligation.',
                'Salah',
                [
                    ['key' => 'prayer_readiness', 'name' => 'Prayer Readiness', 'task_keys' => ['prayer_readiness']],
                    ['key' => 'maghrib_isha', 'name' => 'Maghrib And Isha Target', 'task_keys' => ['maghrib', 'isha']],
                    ['key' => 'add_asr', 'name' => 'Add Asr', 'task_keys' => ['maghrib', 'isha', 'asr']],
                    ['key' => 'add_dhuhr', 'name' => 'Add Dhuhr', 'task_keys' => ['maghrib', 'isha', 'asr', 'dhuhr']],
                    ['key' => 'fajr_readiness', 'name' => 'Fajr Readiness', 'task_keys' => ['maghrib', 'isha', 'asr', 'dhuhr', 'fajr_readiness']],
                    ['key' => 'five_salah', 'name' => 'Five Salah Consistency', 'task_keys' => ['maghrib', 'isha', 'asr', 'dhuhr', 'fajr']],
                    ['key' => 'on_time', 'name' => 'Salah On Time', 'task_keys' => ['maghrib', 'isha', 'asr', 'dhuhr', 'fajr', 'salah_on_time']],
                    ['key' => 'calmness', 'name' => 'Salah Calmness', 'task_keys' => ['maghrib', 'isha', 'asr', 'dhuhr', 'fajr', 'salah_calmness']],
                    ['key' => 'group_prayer', 'name' => 'Family / Group / Masjid Prayer', 'task_keys' => ['group_prayer']],
                    ['key' => 'after_salah_adhkar', 'name' => 'Selected After-Salah Adhkar', 'task_keys' => ['after_salah_adhkar']],
                ],
                tasks: [
                    $this->task('prayer_readiness', 'Prayer Readiness', 'Join or observe family prayer politely when assigned.', 1),
                    $this->task('maghrib', 'Maghrib', 'Pray Maghrib today. Mark done when you finish.', 2),
                    $this->task('isha', 'Isha', 'Pray Isha today. Mark done when you finish.', 3),
                    $this->task('asr', 'Asr', 'Pray Asr today. Mark done when you finish.', 4),
                    $this->task('dhuhr', 'Dhuhr', 'Pray Dhuhr today. Mark done when you finish.', 5),
                    $this->task('fajr_readiness', 'Fajr Readiness', 'Work on Fajr readiness by sleeping earlier and preparing to wake up when suitable.', 6),
                    $this->task('fajr', 'Fajr', 'Pray Fajr today. Mark done when you finish.', 7),
                    $this->task('salah_on_time', 'Salah On Time', 'Complete your salah target with stronger attention to timing.', 8),
                    $this->task('salah_calmness', 'Salah Calmness', 'Complete salah calmly without rushing.', 9),
                    $this->task('group_prayer', 'Family / Group / Masjid Prayer', 'Pray with family, a group, or in the masjid when it is suitable today.', 10),
                    $this->task('after_salah_adhkar', 'Selected After-Salah Adhkar', 'Complete the after-salah adhkar selected by your parent or teacher.', 11),
                ],
            ),
            $this->versionedRoutine(
                'mdj-wudu',
                'My Deen Journey',
                'Wudu',
                'Wudu practice, independence, and calmness.',
                'Wudu Practice',
                [
                    ['key' => 'with_support', 'name' => 'Wudu With Support', 'text' => 'Practise wudu while your parent, teacher, or an adult helps you with the steps.'],
                    ['key' => 'before_salah', 'name' => 'Wudu Before Assigned Salah', 'text' => 'Make wudu before the salah you are practising today.'],
                    ['key' => 'independent', 'name' => 'Independent Wudu', 'text' => 'Make wudu independently, then ask an adult if you are unsure about a step.'],
                    ['key' => 'calm_wudu', 'name' => 'Calm Wudu', 'text' => 'Make wudu calmly without rushing.'],
                    ['key' => 'clean_ready', 'name' => 'Clean And Ready For Salah', 'text' => 'Stay clean and ready for the salah target assigned to you today.'],
                ],
                tasks: [
                    [
                        'key' => 'main',
                        'title' => 'Wudu Practice',
                        'sort_order' => 1,
                        'description' => 'Complete today\'s wudu practice.',
                        'version_descriptions' => [
                            'with_support' => 'Practise wudu while your parent, teacher, or an adult helps you with the steps.',
                            'before_salah' => 'Make wudu before the salah you are practising today.',
                            'independent' => 'Make wudu independently, then ask an adult if you are unsure about a step.',
                            'calm_wudu' => 'Make wudu calmly without rushing.',
                            'clean_ready' => 'Stay clean and ready for the salah target assigned to you today.',
                        ],
                    ],
                ],
            ),
            $this->versionedRoutine(
                'mdj-quran',
                'My Deen Journey',
                'Quran',
                'Generic Quran habit task that does not depend on Quran API integration.',
                'Quran Practice',
                [
                    ['key' => 'listen', 'name' => 'Quran Listening', 'text' => 'Listen carefully to your assigned Quran portion.'],
                    ['key' => 'repeat', 'name' => 'Repeat After Recitation', 'text' => 'Listen to your assigned Quran recitation, then repeat after the reciter or teacher.'],
                    ['key' => 'read_support', 'name' => 'Read With Support', 'text' => 'Read your assigned Quran portion with your teacher, parent, or another adult helping.'],
                    ['key' => 'daily_reading', 'name' => 'Complete Today\'s Quran Reading', 'text' => 'Complete today\'s assigned Quran reading.'],
                    ['key' => 'weekly_3_4', 'name' => 'Quran Habit 3-4 Times Weekly', 'text' => 'Complete your Quran habit 3 or 4 times this week.'],
                    ['key' => 'near_daily', 'name' => 'Daily / Near-Daily Quran Habit', 'text' => 'Complete your Quran habit daily or near-daily this week.'],
                    ['key' => 'careful_reading', 'name' => 'Careful Reading', 'text' => 'Read carefully and correct mistakes patiently.'],
                    ['key' => 'reflection', 'name' => 'Quran Reflection', 'text' => 'Write or say one word, reminder, or lesson from your Quran task.'],
                ],
                tasks: [
                    [
                        'key' => 'main',
                        'title' => 'Quran Practice',
                        'sort_order' => 1,
                        'description' => 'Complete the Quran portion assigned by your teacher or parent today.',
                        'version_descriptions' => [
                            'listen' => 'Listen carefully to your assigned Quran portion.',
                            'repeat' => 'Listen to your assigned Quran recitation, then repeat after the reciter or teacher.',
                            'read_support' => 'Read your assigned Quran portion with your teacher, parent, or another adult helping.',
                            'daily_reading' => 'Complete today\'s assigned Quran reading.',
                            'weekly_3_4' => 'Complete your Quran habit 3 or 4 times this week.',
                            'near_daily' => 'Complete your Quran habit daily or near-daily this week.',
                            'careful_reading' => 'Read carefully and correct mistakes patiently.',
                            'reflection' => 'Write or say one word, reminder, or lesson from your Quran task.',
                        ],
                    ],
                ],
            ),
            $this->versionedRoutine(
                'mdj-dua-practice',
                'My Deen Journey',
                'Dua Practice',
                'Daily dua practice bank from the reviewed adhkar and dua source file; owner review is still required before launch use.',
                'Dua Practice',
                [
                    ['key' => 'one_dua', 'name' => 'Dua V1: 1 Daily-Life Dua', 'task_keys' => $this->bankTaskKeys($this->dailyDuaItems(), 1)],
                    ['key' => 'two_duas', 'name' => 'Dua V2: 2 Daily-Life Duas', 'task_keys' => $this->bankTaskKeys($this->dailyDuaItems(), 2)],
                    ['key' => 'three_duas', 'name' => 'Dua V3: 3 Daily-Life Duas', 'task_keys' => $this->bankTaskKeys($this->dailyDuaItems(), 3)],
                    ['key' => 'four_duas', 'name' => 'Dua V4: 4 Daily-Life Duas', 'task_keys' => $this->bankTaskKeys($this->dailyDuaItems(), 4)],
                    ['key' => 'situation_based', 'name' => 'Dua V5: Food, Sleep, Bathroom, Home, And Wudu', 'task_keys' => $this->dailyDuaSituationKeys()],
                    ['key' => 'quranic_duas', 'name' => 'Dua V6: Add Quranic Duas', 'task_keys' => $this->dailyDuaQuranicKeys()],
                    ['key' => 'salah_related', 'name' => 'Dua V7: Add Salah-Related Dhikr And Duas', 'task_keys' => $this->dailyDuaSalahKeys()],
                    ['key' => 'full_bank', 'name' => 'Dua V8: Full Teacher-Selected Bank', 'task_keys' => $this->bankTaskKeys($this->dailyDuaItems())],
                ],
                tasks: $this->dailyDuaTasks(),
            ),
            $this->versionedRoutine(
                'mdj-morning-adhkar',
                'My Deen Journey',
                'Morning Adhkar',
                'Morning adhkar bank from the reviewed adhkar and dua source file; owner review is still required before launch use.',
                'Morning Adhkar',
                [
                    ['key' => 'one_item', 'name' => 'Morning V1: 1 Short Item', 'task_keys' => $this->bankTaskKeys($this->morningItems(), 1)],
                    ['key' => 'two_items', 'name' => 'Morning V2: 2 Items', 'task_keys' => $this->bankTaskKeys($this->morningItems(), 2)],
                    ['key' => 'three_items', 'name' => 'Morning V3: 3 Items', 'task_keys' => $this->bankTaskKeys($this->morningItems(), 3)],
                    ['key' => 'four_items', 'name' => 'Morning V4: 4 Items', 'task_keys' => $this->bankTaskKeys($this->morningItems(), 4)],
                    ['key' => 'five_items', 'name' => 'Morning V5: 5 Items With Protection', 'task_keys' => $this->bankTaskKeys($this->morningItems(), 5)],
                    ['key' => 'teacher_selected', 'name' => 'Morning V6: 6-7 Teacher-Selected Items', 'task_keys' => $this->bankTaskKeys($this->morningItems(), 7)],
                    ['key' => 'longer_set', 'name' => 'Morning V7: Longer Independent Set', 'task_keys' => $this->bankTaskKeys($this->morningItems(), 12)],
                    ['key' => 'full_routine', 'name' => 'Morning V8: Full Teacher-Selected Routine', 'task_keys' => $this->bankTaskKeys($this->morningItems())],
                ],
                tasks: $this->morningAdhkarTasks(),
            ),
            $this->versionedRoutine(
                'mdj-evening-adhkar',
                'My Deen Journey',
                'Evening Adhkar',
                'Evening and bedtime adhkar bank from the reviewed adhkar and dua source file; owner review is still required before launch use.',
                'Evening Adhkar',
                [
                    ['key' => 'one_item', 'name' => 'Evening V1: 1 Short Item', 'task_keys' => $this->bankTaskKeys($this->eveningItems(), 1)],
                    ['key' => 'two_items', 'name' => 'Evening V2: 2 Items', 'task_keys' => $this->bankTaskKeys($this->eveningItems(), 2)],
                    ['key' => 'three_items', 'name' => 'Evening V3: 3 Items', 'task_keys' => $this->bankTaskKeys($this->eveningItems(), 3)],
                    ['key' => 'four_items', 'name' => 'Evening V4: 4 Items', 'task_keys' => $this->bankTaskKeys($this->eveningItems(), 4)],
                    ['key' => 'five_items', 'name' => 'Evening V5: 5 Items With Protection', 'task_keys' => $this->bankTaskKeys($this->eveningItems(), 5)],
                    ['key' => 'teacher_selected', 'name' => 'Evening V6: 6-7 Teacher-Selected Items', 'task_keys' => $this->bankTaskKeys($this->eveningItems(), 7)],
                    ['key' => 'bedtime_set', 'name' => 'Evening V7: Evening And Bedtime Items', 'task_keys' => $this->bankTaskKeys($this->eveningItems(), 12)],
                    ['key' => 'full_routine', 'name' => 'Evening V8: Full Teacher-Selected Routine', 'task_keys' => $this->bankTaskKeys($this->eveningItems())],
                ],
                tasks: $this->eveningAdhkarTasks(),
            ),
            $this->versionedRoutine(
                'mdj-prayer-adab',
                'My Deen Journey',
                'Masjid / Prayer Adab',
                'Prayer-place manners without assuming every student goes to the masjid daily.',
                'Prayer Adab',
                [
                    ['key' => 'prayer_manners', 'name' => 'Prayer Manners', 'task_keys' => ['quiet_respectful']],
                    ['key' => 'prepare_place', 'name' => 'Prepare For Prayer Place', 'task_keys' => ['quiet_respectful', 'prepare_prayer_place']],
                    ['key' => 'good_manners', 'name' => 'Good Manners In Prayer Place', 'task_keys' => ['quiet_respectful', 'prepare_prayer_place', 'good_manners']],
                    ['key' => 'listen_reminders', 'name' => 'Listen To Islamic Reminders', 'task_keys' => ['quiet_respectful', 'listen_reminder']],
                    ['key' => 'clean_space', 'name' => 'Keep Prayer Space Clean', 'task_keys' => ['quiet_respectful', 'clean_prayer_space']],
                ],
                recurrenceKind: 'weekly',
                tasks: [
                    $this->task('quiet_respectful', 'Prayer Manners', 'Practise quiet and respectful manners during prayer time at home, in class, or in the masjid.', 1),
                    $this->task('prepare_prayer_place', 'Prepare For Prayer Place', 'Prepare well before going to the masjid or prayer place when this applies today.', 2),
                    $this->task('good_manners', 'Good Manners In Prayer Place', 'Use good manners in the masjid or prayer place.', 3),
                    $this->task('listen_reminder', 'Listen To Islamic Reminders', 'Listen carefully to an Islamic reminder or learning moment this week.', 4),
                    $this->task('clean_prayer_space', 'Keep Prayer Space Clean', 'Help keep the prayer space clean and respectful.', 5),
                ],
            ),
            $this->versionedRoutine(
                'mdj-weekly-reflection',
                'My Deen Journey',
                'Deen Reflection',
                'Weekly reflection without turning character into fake daily checkboxes.',
                'Weekly Deen Reflection',
                [
                    ['key' => 'weekly_reflection', 'name' => 'Weekly Deen Reflection', 'task_keys' => ['weekly_reflection']],
                    ['key' => 'one_good_action', 'name' => 'One Good Action', 'task_keys' => ['weekly_reflection', 'good_action']],
                    ['key' => 'one_improvement', 'name' => 'One Thing To Improve', 'task_keys' => ['weekly_reflection', 'good_action', 'improve']],
                ],
                recurrenceKind: 'weekly',
                tasks: [
                    $this->task('weekly_reflection', 'Weekly Deen Reflection', 'What did you practise this week from your Islamic learning?', 1),
                    $this->task('good_action', 'One Good Action', 'Choose one good action you did this week and explain it briefly.', 2),
                    $this->task('improve', 'One Thing To Improve', 'What is one Deen habit you want to improve next week?', 3),
                ],
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
                'general_library_seed' => [
                    'folder_path' => ['My Deen Journey', 'Dua Bank'],
                    'resources' => $this->duaBankLibraryResources(),
                ],
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
                fn (array $version): array => array_filter([
                    'key' => $version['key'],
                    'display_name' => $version['name'] ?? $version['display_name'],
                    'task_keys' => $version['task_keys'] ?? null,
                ], fn ($value): bool => $value !== null),
                $versions
            ),
            'tasks' => array_values($tasks),
        ];
    }

    private function task(string $key, string $title, string $description, int $sortOrder): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'sort_order' => $sortOrder,
        ];
    }

    private function dailyDuaTasks(): array
    {
        return $this->bankTasks($this->dailyDuaItems(), 'Dua');
    }

    private function morningAdhkarTasks(): array
    {
        return $this->bankTasks($this->morningItems(), 'Morning Dhikr');
    }

    private function eveningAdhkarTasks(): array
    {
        return $this->bankTasks($this->eveningItems(), 'Evening Dhikr');
    }

    /** @return array<int, array<string, mixed>> */
    private function morningItems(): array
    {
        return app(AdhkarDuaBankCatalog::class)->morningItems();
    }

    /** @return array<int, array<string, mixed>> */
    private function eveningItems(): array
    {
        return app(AdhkarDuaBankCatalog::class)->eveningItems();
    }

    /** @return array<int, array<string, mixed>> */
    private function dailyDuaItems(): array
    {
        return app(AdhkarDuaBankCatalog::class)->duaItems();
    }

    /** @param array<int, array<string, mixed>> $items */
    private function bankTaskKeys(array $items, ?int $limit = null): array
    {
        $keys = array_map(fn (array $item): string => (string) $item['key'], $items);

        return $limit === null ? $keys : array_slice($keys, 0, $limit);
    }

    private function dailyDuaSituationKeys(): array
    {
        // These category slices are part of the reviewed manifest contract. If a
        // future bank edit empties one slice, fail the install instead of
        // publishing a version whose wording promises tasks that do not exist.
        return $this->dailyDuaKeysMatching(
            fn (array $item): bool => $this->bankCategoryContains($item, ['sleep', 'bathroom', 'wudu', 'food', 'home'])
        );
    }

    private function dailyDuaQuranicKeys(): array
    {
        return $this->dailyDuaKeysMatching(
            fn (array $item): bool => $this->bankCodeNumber($item) <= 20
                || $this->bankCategoryContains($item, ['quranic dua'])
        );
    }

    private function dailyDuaSalahKeys(): array
    {
        return $this->dailyDuaKeysMatching(
            fn (array $item): bool => $this->bankCodeNumber($item) <= 20
                || $this->bankCategoryContains($item, ['salah', 'adhan', 'witr'])
        );
    }

    /** @param array<string, mixed> $item */
    private function bankCodeNumber(array $item): int
    {
        return (int) ($item['code_number'] ?? 999);
    }

    private function dailyDuaKeysMatching(callable $callback): array
    {
        return array_values(array_map(
            fn (array $item): string => (string) $item['key'],
            array_filter($this->dailyDuaItems(), $callback)
        ));
    }

    /** @param array<string, mixed> $item */
    private function bankCategoryContains(array $item, array $needles): bool
    {
        $category = strtolower((string) ($item['category'] ?? ''));

        foreach ($needles as $needle) {
            if (str_contains($category, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, array<string, mixed>> $items */
    private function bankTasks(array $items, string $titlePrefix): array
    {
        return array_map(
            fn (array $item): array => $this->task(
                (string) $item['key'],
                $titlePrefix.': '.(string) $item['title'],
                $this->bankDescription($item),
                (int) $item['sort_order']
            ),
            $items
        );
    }

    /** @param array<string, mixed> $item */
    private function bankDescription(array $item): string
    {
        $lines = [
            'Arabic:',
            (string) ($item['arabic'] ?? ''),
            '',
            'Meaning: '.(string) ($item['english_meaning'] ?? ''),
        ];

        foreach ([
            'Repeat' => $item['repeat'] ?? null,
            'Level' => $item['level'] ?? null,
            'Category' => $item['category'] ?? null,
            'Quran ref' => $item['quran_ref'] ?? null,
            'Source' => $item['source'] ?? null,
            'Source URL' => $item['source_url'] ?? null,
        ] as $label => $value) {
            if (filled($value)) {
                $lines[] = $label.': '.$value;
            }
        }

        return implode("\n", $lines);
    }

    private function duaBankLibraryResources(): array
    {
        return array_map(function (array $item): array {
            return [
                'code' => $item['code'],
                'title' => $item['code'].' - '.$item['title'],
                'description' => $this->bankShortDescription($item),
                'text_content' => $this->bankDescription($item),
                'source_url' => $item['source_url'] ?? null,
                'sort_order' => $item['sort_order'],
            ];
        }, $this->dailyDuaItems());
    }

    /** @param array<string, mixed> $item */
    private function bankShortDescription(array $item): string
    {
        return trim(implode(' | ', array_filter([
            $item['level'] ?? null,
            $item['category'] ?? null,
            filled($item['repeat'] ?? null) ? 'Repeat '.$item['repeat'] : null,
            $item['quran_ref'] ?? null,
        ])));
    }
}
