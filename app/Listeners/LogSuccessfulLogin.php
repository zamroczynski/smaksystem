<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (config('auth.log')) {
            Log::info('EVENT [Login]: Użytkownik pomyślnie uwierzytelniony i sesja utworzona.', [
                'user_id' => $event->user->id,
                'user_login' => $event->user->login,
                'guard' => $event->guard,
            ]);
        }
    }
}
