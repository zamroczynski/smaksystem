<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RolesController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService) // Wstrzyknij serwis
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        $showDisabled = $request->boolean('show_disabled');

        $rolesQuery = Role::orderBy('id');

        if ($showDisabled) {
            $rolesQuery->withTrashed(); 
        }

        $roles = $rolesQuery->get();

        // Przetwórz role, dodając status przypisania
        // Wciąż musimy uważać na role usunięte, które nie mają nazwy w Spatie Permissions
        $rolesWithAssignmentStatus = $roles->map(function ($role) {
            // Sprawdź is_assigned_to_users tylko dla aktywnych ról
            $role->is_assigned_to_users = ($role->deleted_at === null)
                                            ? User::role($role->name)->exists()
                                            : false; // Usunięta rola nie jest przypisana
            return $role;
        });

        return Inertia::render('Roles/Index', [
            'roles' => $rolesWithAssignmentStatus->values(),
            'flash' => session('flash'),
            'show_disabled' => $showDisabled,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all(['id', 'name']); 

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $validatedData = $request->validated();

        $this->roleService->createRoleWithPermissions(
            $validatedData['name'],
            $validatedData['permissions'] ?? []
        );

        return to_route('roles.index')
            ->with('success', 'Rola została pomyślnie dodana.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $allPermissions = Permission::all(['id', 'name']);
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return Inertia::render('Roles/Edit', [
            'role' => $role->only('id', 'name'),
            'allPermissions' => $allPermissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validatedData = $request->validated();

        $this->roleService->updateRole(
            $role, // Przekaż obiekt roli
            $validatedData['name'],
            $validatedData['permissions'] ?? []
        );

        return to_route('roles.index')
            ->with('success', 'Rola została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Kierownik') {
            return to_route('roles.index')->with('error', 'Nie możesz wyłączyć roli "Kierownik"!');
        }

        // Sprawdź, czy użytkownik próbuje usunąć rolę, którą sam posiada
        if (Auth::hasRole($role->name)) {
             return to_route('roles.index')->with('error', 'Nie możesz wyłączyć roli, którą sam posiadasz!');
        }

        $this->roleService->destroyRole($role);

        return to_route('roles.index')
            ->with('success', 'Rola została pomyślnie wyłączona.');
    }

    /**
     * Restore the specified soft-deleted role.
     */
    public function restore(int $roleId) // Przyjmujemy ID, bo rola może być już usunięta
    {
        $role = $this->roleService->restoreRole($roleId);

        if ($role) {
            return to_route('roles.index')->with('success', 'Rola została pomyślnie przywrócona.');
        }

        return to_route('roles.index')->with('error', 'Nie udało się przywrócić roli.');
    }
}
