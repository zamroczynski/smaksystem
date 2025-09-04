<?php

namespace App\Providers\Auth;

use App\Models\Scopes\ProtectedRecordScope;
use Illuminate\Auth\EloquentUserProvider;

class ProtectedUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->model::withoutGlobalScope(ProtectedRecordScope::class)
            ->find($identifier);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // dd('ProtectedUserProvider został wywołany z danymi:', $credentials);
        $query = $this->model::withoutGlobalScope(ProtectedRecordScope::class);

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }
}
