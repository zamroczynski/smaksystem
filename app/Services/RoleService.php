<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Creates a new role and assigns permissions to it.
     *
     * @param  string  $roleName  Role name.
     * @param  array  $permissionNames  Table of names of permissions to assign.
     * @return Role Newly created role.
     */
    public function createRoleWithPermissions(string $roleName, array $permissionNames): Role
    {
        return DB::transaction(function () use ($roleName, $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if (! empty($permissionNames)) {
                $role->givePermissionTo($permissionNames);
            }

            return $role;
        });
    }

    /**
     * Updates an existing role and synchronizes its permissions.
     *
     * @param  Role  $role  Role object to be updated.
     * @param  string  $roleName  New role name.
     * @param  array  $permissionNames  Table of names of permissions to assign.
     * @return Role Updated role.
     */
    public function updateRole(Role $role, string $roleName, array $permissionNames): Role
    {
        return DB::transaction(function () use ($role, $roleName, $permissionNames) {
            if ($role->name !== $roleName) {
                $role->update(['name' => $roleName]);
            }

            $role->syncPermissions($permissionNames);

            return $role;
        });
    }

    /**
     * Softly removes the role and disconnects it from all users.
     *
     * @param  Role  $role  Role object to be deleted.
     * @return bool True, if deletion was successful.
     */
    public function destroyRole(Role $role): bool
    {
        return DB::transaction(function () use ($role) {
            $usersWithRole = User::role($role->name)->get();

            if ($usersWithRole->isNotEmpty()) {
                foreach ($usersWithRole as $user) {
                    $user->removeRole($role->name);
                }
            }

            return $role->delete();
        });
    }

    /**
     * Checks if the role is assigned to any users.
     */
    public function isRoleAssignedToUsers(Role $role): bool
    {
        return User::role($role->name)->exists();
    }

    /**
     * Restores the gently removed role.
     *
     * @param  int  $roleId  ID of the role to be restored.
     * @return Role|null The restored role or null if not found.
     */
    public function restoreRole(int $roleId): ?Role
    {
        $role = Role::withTrashed()->find($roleId);

        if ($role) {
            $role->restore();
        }

        return $role;
    }

    /**
     * Retrieves paginated roles with filtering and sorting options.
     */
    public function getPaginatedRoles(array $options)
    {
        $query = Role::query()->where('is_system_protected', false);

        if ($options['show_disabled']) {
            $query->onlyTrashed();
        }

        if (! empty($options['filter'])) {
            $query->where('name', 'ILIKE', '%'.$options['filter'].'%');
        }

        $query->orderBy($options['sort'], $options['direction']);

        $roles = $query->paginate(10)->appends($options);

        $roles->through(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'deleted_at' => $role->deleted_at,
                'is_assigned_to_users' => $role->users()->exists(),
            ];
        });

        return $roles;
    }
}
