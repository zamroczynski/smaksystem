<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Inertia\Inertia; 
use App\Services\UserService; 
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Auth;
use App\Helpers\BreadcrumbsGenerator;

class UsersController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $showDisabled = $request->boolean('show_disabled');
        $filter = $request->input('filter');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        if (! in_array($sort, ['id', 'name', 'email', 'roles', 'created_at', 'deleted_at'])) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $usersQuery = User::query();

        $usersQuery->orderBy($sort, $direction);

        if ($showDisabled) {
            $usersQuery->onlyTrashed();
        }

        if ($filter) {
            $usersQuery->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter . '%')
                      ->orWhere('email', 'like', '%' . $filter . '%')
                      ->orWhereHas('roles', function ($q) use ($filter) {
                          $q->where('name', 'like', '%' . $filter . '%');
                      });
            });
        }

        $users = $usersQuery->paginate(10)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]); 

        $users->through(function ($user) {
            $user->load('roles');
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i') : null,
                'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i') : null,
                'deleted_at' => $user->deleted_at,
            ];
        });

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Pracownikami', route('users.index'))
            ->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'flash' => session('flash'),
            'show_disabled' => $showDisabled,
            'breadcrumbs' => $breadcrumbs,
            'filter' => $filter,
            'sort_by' => $sort,
            'sort_direction' => $direction,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all(['id', 'name']);
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Pracownikami', route('users.index'))
            ->add('Dodaj pracownika', route('users.create'))
            ->get();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validatedData = $request->validated();
        $this->userService->create($validatedData);

        return to_route('users.index')
            ->with('success', 'Użytkownik został pomyślnie dodany.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all(['id', 'name']);
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Pracownikami', route('users.index'))
            ->add('Edytuj pracownika', route('users.edit', $user))
            ->get();

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'login' => $user->login,
                'email' => $user->email,
                'current_role' => $user->getRoleNames()->first(),
            ],
            'roles' => $roles,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        $this->userService->update($user, $validatedData);

        return redirect()->route('users.index')->with('success', 'Dane pracownika zostały pomyślnie zaktualizowane.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Nie możesz usunąć samego siebie!');
        }

        if ($user->hasRole('admin')) {
            $adminRole = Role::where('name', 'Kierownik')->first(); 
            if ($adminRole) {
                $activeAdminsCount = User::role($adminRole->name)->whereNull('deleted_at')->count();

                if ($activeAdminsCount <= 1) {
                    return redirect()->route('users.index')->with('error', 'Nie możesz wyłączyć ostatniego aktywnego administratora!');
                }
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pracownik został pomyślnie wyłączony.');
    }

    /**
     * Restore the specified soft-deleted user.
     */
    public function restore(int $userId)
    {
        $user = User::withTrashed()->find($userId);

        if ($user) {
            $user->restore();

            return to_route('users.index')->with('success', 'Użytkownik został pomyślnie przywrócony.');
        }

        return to_route('users.index')->with('error', 'Nie udało się przywrócić użytkownika.');
    }
}
