<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Holiday Generation Schedule
    |--------------------------------------------------------------------------
    |
    | Here you can specify when the command to generate next year's holidays
    | should be run. The values are pulled from your .env file, with
    | December 10th as the default fallback.
    |
    */

    'generation_month' => env('HOLIDAY_GENERATION_MONTH', 12),

    'generation_day' => env('HOLIDAY_GENERATION_DAY', 10),
];
