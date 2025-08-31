<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Creates a new user and assigns a role to them.
     *
     * @param  array  $data  Data from the request (validated).
     * @return User Newly created user.
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
     * Updates user data and roles.
     *
     * @param  User  $user  User to update.
     * @param  array  $data  Data from the request (validated).
     * @return User Updated user.
     */
    public function update(User $user, array $data): User
    {
        DB::transaction(function () use ($user, $data) {
            $user->name = $data['name'];
            $user->login = $data['login'];
            $user->email = $data['email'] ?? null;

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if (isset($data['role_name']) && ! is_null($data['role_name'])) {
                $user->syncRoles([$data['role_name']]);
            } else {
                $user->syncRoles([]);
            }
        });

        return $user;
    }

    /**
     * Retrieves a paginated list of users with filtering and sorting.
     *
     * @param  array  $options  Filtering and sorting options.
     */
    public function getPaginatedUsers(array $options): LengthAwarePaginator
    {
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
     * Applies search filters to the query.
     */
    protected function applyFilters(Builder $query, ?string $filter): Builder
    {
        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $lowerCaseFilter = strtolower($filter);
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
     * Applies sorting to the query.
     */
    protected function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        if ($sort === 'roles') {
            $query->select('users.*')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->orderBy('roles.name', $direction);
        } else {
            $query->orderBy('users.'.$sort, $direction);
        }

        return $query;
    }

    /**
     * Searches for users based on a string of characters and formats the results
     * in the form of a collection ready for use in a combobox component.
     *
     * @param  string  $query  Search string.
     */
    public function searchForCombobox(string $query = ''): Collection
    {
        $userQuery = User::query();

        if (empty($query)) {
            $userQuery->orderBy('id', 'desc');
        } else {
            $userQuery->where('name', 'ILIKE', "%{$query}%");
        }

        return $userQuery
            ->select(['id', 'name'])
            ->take(10)
            ->get()
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => $user->name,
            ]);
    }
}
