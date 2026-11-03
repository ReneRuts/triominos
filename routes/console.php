<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('games:cleanup')->hourly();
