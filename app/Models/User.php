<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Scopes\ProtectedRecordScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::addGlobalScope(new ProtectedRecordScope);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'two_factor_enabled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the column name for the "username" to be used by the authentication.
     *
     * @return string
     */
    public function findForAuth($username)
    {
        return static::where('login', $username)->first();
    }

    /**
     * Get the preferences for the user.
     */
    public function preferences(): HasMany
    {
        return $this->hasMany(Preference::class);
    }

    /**
     * Get the schedule assignments for the user.
     */
    public function scheduleAssignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    /**
     * Determine if two-factor authentication is enabled for the user.
     *
     * @return bool
     */
    public function getTwoFactorEnabledAttribute()
    {
        return ! is_null($this->two_factor_secret) &&
               ! is_null($this->two_factor_confirmed_at);
    }
}
