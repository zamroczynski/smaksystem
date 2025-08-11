<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Tworzy nową rolę i przypisuje jej uprawnienia.
     *
     * @param  string  $roleName  Nazwa roli.
     * @param  array  $permissionNames  Tablica nazw uprawnień do przypisania.
     * @return Role Nowo utworzona rola.
     */
    public function createRoleWithPermissions(string $roleName, array $permissionNames): Role
    {
        return DB::transaction(function () use ($roleName, $permissionNames) {
            // Upewnij się, że rola nie istnieje przed utworzeniem
            $role = Role::firstOrCreate(['name' => $roleName]);

            // Przypisz uprawnienia do roli
            if (! empty($permissionNames)) {
                $role->givePermissionTo($permissionNames);
            }

            return $role;
        });
    }

    /**
     * Aktualizuje istniejącą rolę i synchronizuje jej uprawnienia.
     *
     * @param  Role  $role  Obiekt roli do zaktualizowania.
     * @param  string  $roleName  Nowa nazwa roli.
     * @param  array  $permissionNames  Tablica nazw uprawnień do przypisania.
     * @return Role Zaktualizowana rola.
     */
    public function updateRole(Role $role, string $roleName, array $permissionNames): Role
    {
        return DB::transaction(function () use ($role, $roleName, $permissionNames) {
            // Zaktualizuj nazwę roli, jeśli się zmieniła
            if ($role->name !== $roleName) {
                $role->update(['name' => $roleName]);
            }

            // Zsynchronizuj uprawnienia roli
            $role->syncPermissions($permissionNames);

            return $role;
        });
    }

    /**
     * Miękko usuwa rolę i odłącza ją od wszystkich użytkowników.
     *
     * @param  Role  $role  Obiekt roli do usunięcia.
     * @return bool True, jeśli usunięcie się powiodło.
     */
    public function destroyRole(Role $role): bool
    {
        return DB::transaction(function () use ($role) {
            // Sprawdź, czy rola jest przypisana do jakichkolwiek użytkowników
            $usersWithRole = User::role($role->name)->get();

            if ($usersWithRole->isNotEmpty()) {
                // Jeśli są użytkownicy z tą rolą, odepnij ją od nich
                foreach ($usersWithRole as $user) {
                    $user->removeRole($role->name);
                }
            }

            return $role->delete();
        });
    }

    /**
     * Sprawdza, czy rola jest przypisana do jakichkolwiek użytkowników.
     */
    public function isRoleAssignedToUsers(Role $role): bool
    {
        return User::role($role->name)->exists();
    }

    /**
     * Przywraca miękko usuniętą rolę.
     *
     * @param  int  $roleId  ID roli do przywrócenia.
     * @return Role|null Przywrócona rola lub null, jeśli nie znaleziono.
     */
    public function restoreRole(int $roleId): ?Role
    {
        $role = Role::withTrashed()->find($roleId);

        if ($role) {
            $role->restore();
        }

        return $role;
    }
}
