<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
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
            $rolesQuery->onlyTrashed();
        }

        $roles = $rolesQuery->paginate(10)->appends([
            'show_disabled' => $showDisabled,
        ]);

        $roles->through(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'deleted_at' => $role->deleted_at,
                'is_assigned_to_users' => $role->users()->exists(),
            ];
        });

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Rolami', route('roles.index'))
            ->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'flash' => session('flash'),
            'show_disabled' => $showDisabled,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all(['id', 'name']);

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Rolami', route('roles.index'))
            ->add('Dodaj nową rolę', route('roles.create'))
            ->get();

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
            'breadcrumbs' => $breadcrumbs,
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

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Rolami', route('roles.index'))
            ->add('Edytuj rolę', route('roles.edit', $role))
            ->get();

        return Inertia::render('Roles/Edit', [
            'role' => $role->only('id', 'name'),
            'allPermissions' => $allPermissions,
            'rolePermissions' => $rolePermissions,
            'breadcrumbs' => $breadcrumbs,
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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Sprawdź, czy użytkownik próbuje usunąć rolę, którą sam posiada
        if ($user->hasRole($role->name)) {
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
