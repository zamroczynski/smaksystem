<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Tworzy nowego użytkownika i przypisuje mu rolę.
     *
     * @param array $data Dane z requestu (walidowane).
     * @return User Nowo utworzony użytkownik.
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'login' => $data['login'],
                'email' => $data['email'] ?? null, // Obsługa nullable email
                'password' => Hash::make($data['password']),
            ]);

            // Przypisz rolę, jeśli została wybrana i nie jest null
            if (isset($data['role_name']) && !is_null($data['role_name'])) {
                $user->assignRole($data['role_name']);
            }

            return $user;
        });
    }

    /**
     * Aktualizuje dane użytkownika i jego role.
     *
     * @param User $user Użytkownik do zaktualizowania.
     * @param array $data Dane z requestu (walidowane).
     * @return User Zaktualizowany użytkownik.
     */
    public function update(User $user, array $data): User
    {
        DB::transaction(function () use ($user, $data) {
            $user->name = $data['name'];
            $user->login = $data['login'];
            $user->email = $data['email'] ?? null;

            // Zmień hasło tylko, jeśli zostało podane
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if (isset($data['role_name']) && !is_null($data['role_name'])) {
                $user->syncRoles([$data['role_name']]);
            } else {
                // Jeśli rola nie została wybrana lub ustawiono null (dla "Brak roli"), usuń wszystkie role
                $user->syncRoles([]);
            }
        });

        return $user;
    }
}