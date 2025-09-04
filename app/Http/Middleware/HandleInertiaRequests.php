<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\Models\Permission;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $rolePermissions = [];

        $superAdminRoleName = config('app.super_admin_role_name', 'Super Admin');
        if ($user) {
            $superAdminRoleName = config('app.super_admin_role_name', 'Super Admin');

            if ($user->hasRole($superAdminRoleName)) {
                $rolePermissions = Permission::pluck('name')->toArray();
            } else {
                if ($user && $user->roles->isNotEmpty()) {
                    /** @var App\Models\Role $firstRole */
                    $firstRole = $user->roles->first();
                    $rolePermissions = $firstRole->permissions->pluck('name')->toArray();
                }
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'role_permissions' => $rolePermissions,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
