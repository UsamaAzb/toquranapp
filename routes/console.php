<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('daily:publish-midnight')
    ->dailyAt('00:00')
    ->timezone(config('app.timezone'));

Schedule::command('tasks:auto-approve-trusted-children')
    ->dailyAt('00:10')
    ->timezone(config('app.timezone'));
