<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Tworzy nowego użytkownika i przypisuje mu rolę.
     *
     * @param  array  $data  Dane z requestu (walidowane).
     * @return User Nowo utworzony użytkownik.
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'login' => $data['login'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            if (isset($data['role_name']) && ! is_null($data['role_name'])) {
                $user->assignRole($data['role_name']);
            }

            return $user;
        });
    }

    /**
     * Aktualizuje dane użytkownika i jego role.
     *
     * @param  User  $user  Użytkownik do zaktualizowania.
     * @param  array  $data  Dane z requestu (walidowane).
     * @return User Zaktualizowany użytkownik.
     */
    public function update(User $user, array $data): User
    {
        DB::transaction(function () use ($user, $data) {
            $user->name = $data['name'];
            $user->login = $data['login'];
            $user->email = $data['email'] ?? null;

            // Zmień hasło tylko, jeśli zostało podane
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if (isset($data['role_name']) && ! is_null($data['role_name'])) {
                $user->syncRoles([$data['role_name']]);
            } else {
                // Jeśli rola nie została wybrana lub ustawiono null (dla "Brak roli"), usuń wszystkie role
                $user->syncRoles([]);
            }
        });

        return $user;
    }

    /**
     * Pobiera paginowaną listę użytkowników z filtrowaniem i sortowaniem.
     *
     * @param  array  $options  Opcje filtrowania i sortowania.
     */
    public function getPaginatedUsers(array $options): LengthAwarePaginator
    {
        Log::info('Opcje paginacji otrzymane z kontrolera:', $options);

        $showDisabled = $options['show_disabled'] ?? false;
        $filter = $options['filter'] ?? null;
        $sort = $options['sort'] ?? 'id';
        $direction = $options['direction'] ?? 'asc';

        if (! in_array($sort, ['id', 'name', 'email', 'roles', 'created_at', 'deleted_at'])) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $usersQuery = User::query();

        $usersQuery = $this->applyFilters($usersQuery, $filter);
        $usersQuery = $this->applySorting($usersQuery, $sort, $direction);

        if ($showDisabled) {
            $usersQuery->onlyTrashed();
        }

        Log::info('Konstruowane zapytanie do bazy danych:', ['sql' => $usersQuery->toSql(), 'bindings' => $usersQuery->getBindings()]);

        $users = $usersQuery->paginate(10)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]);

        return $users->through(function ($user) {
            $user->load('roles');

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->first(),
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i') : null,
                'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i') : null,
                'deleted_at' => $user->deleted_at,
            ];
        });
    }

    /**
     * Stosuje filtry wyszukiwania do zapytania.
     */
    protected function applyFilters(Builder $query, ?string $filter): Builder
    {
        Log::info('Zastosowanie filtra:', ['filter' => $filter]);
        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $lowerCaseFilter = strtolower($filter);
                Log::info('Wyszukiwany tekst po konwersji:', ['lowerCaseFilter' => $lowerCaseFilter]);
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.$lowerCaseFilter.'%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.$lowerCaseFilter.'%'])
                    ->orWhereHas('roles', function ($q) use ($lowerCaseFilter) {
                        $q->whereRaw('LOWER(name) LIKE ?', ['%'.$lowerCaseFilter.'%']);
                    });
            });
        }

        return $query;
    }

    /**
     * Stosuje sortowanie do zapytania.
     */
    protected function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        Log::info('Zastosowanie sortowania:', ['sort' => $sort, 'direction' => $direction]);
        if ($sort === 'roles') {
            Log::info('Sortowanie po rolach: Wykonywany JOIN.');
            $query->select('users.*')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->orderBy('roles.name', $direction);
        } else {
            Log::info('Sortowanie po innej kolumnie niż role.');
            $query->orderBy('users.'.$sort, $direction);
        }

        return $query;
    }
}
