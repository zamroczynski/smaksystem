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

        $usersQuery = User::orderBy('id');

        if ($showDisabled) {
            $usersQuery->onlyTrashed();
        }

        $users = $usersQuery->paginate(10)->appends([
            'show_disabled' => $showDisabled,
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


        return Inertia::render('Users/Index', [
            'users' => $users,
            'flash' => session('flash'),
            'show_disabled' => $showDisabled,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        // Pobierz wszystkie dostępne role dla Selecta
        $roles = Role::all(['id', 'name']); // Upewnij się, że model Role jest zaimportowany

        return Inertia::render('Users/Create', [
            'roles' => $roles,
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

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'login' => $user->login,
                'email' => $user->email,
                // Pobierz bieżącą rolę użytkownika, jeśli istnieje
                'current_role' => $user->getRoleNames()->first(),
            ],
            'roles' => $roles, // Przekaż wszystkie dostępne role
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
        // Sprawdź, czy użytkownik próbuje usunąć samego siebie
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Nie możesz usunąć samego siebie!');
        }

        if ($user->hasRole('admin')) {
             // Sprawdź, ile aktywnych użytkowników ma rolę 'Kierownik'
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
