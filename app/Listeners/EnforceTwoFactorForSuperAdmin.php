<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class EnforceTwoFactorForSuperAdmin
{
    /**
     * Create the event listener.
     */
    public function __construct(private Request $request) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $superAdminRoleName = config('app.super_admin_role_name', 'Super Admin');
        
        if ($user->hasRole($superAdminRoleName)) {
            if (is_null($user->two_factor_secret)) {
                
                Auth::logout();
                $this->request->session()->invalidate();
                $this->request->session()->regenerateToken();

                abort(redirect()->route('login')->withErrors([
                    'login' => 'Twoje konto wymaga konfiguracji 2FA. Skontaktuj się z administratorem systemu, aby dokończyć konfigurację.'
                ]));
            }
        }
    }
}
