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
    public function __construct(private RoleService $roleService) {}

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        $options = [
            'show_disabled' => $request->boolean('show_disabled'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
        ];

        $roles = $this->roleService->getPaginatedRoles($options);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'flash' => session('flash'),
            'show_disabled' => $options['show_disabled'],
            'breadcrumbs' => $this->getRolesBreadcrumbs(),
            'filter' => $options['filter'],
            'sort_by' => $options['sort'],
            'sort_direction' => $options['direction'],
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return Inertia::render('Roles/Create', [
            'permissions' => Permission::all(['id', 'name']),
            'breadcrumbs' => $this->getRolesBreadcrumbs('Dodaj nową rolę', route('roles.create')),
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
        return Inertia::render('Roles/Edit', [
            'role' => $role->only('id', 'name'),
            'allPermissions' => Permission::all(['id', 'name']),
            'rolePermissions' => $role->permissions->pluck('name')->toArray(),
            'breadcrumbs' => $this->getRolesBreadcrumbs('Edytuj rolę', route('roles.edit', $role)),
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validatedData = $request->validated();

        $this->roleService->updateRole(
            $role,
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
    public function restore(int $roleId)
    {
        $role = $this->roleService->restoreRole($roleId);

        if ($role) {
            return to_route('roles.index')->with('success', 'Rola została pomyślnie przywrócona.');
        }

        return to_route('roles.index')->with('error', 'Nie udało się przywrócić roli.');
    }

    /**
     * Generates breadcrumbs for role management.
     */
    protected function getRolesBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Rolami', route('roles.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
