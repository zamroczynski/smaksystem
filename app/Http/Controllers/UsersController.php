<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia; 
use Spatie\Permission\Models\Role;
use App\Services\UserService; 
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;



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
    public function index()
    {
        $users = User::with('roles')
                     ->paginate(10)
                     ->through(fn ($user) => [
                         'id' => $user->id,
                         'name' => $user->name,
                         'login' => $user->login,
                         'email' => $user->email,
                         // Sformatuj datę created_at
                         'created_at' => $user->created_at->format('Y-m-d H:i:s'), // Formatowanie daty
                         // Pobierz nazwy ról i połącz je w string
                         'roles' => $user->roles->pluck('name')->implode(', '),
                     ]);

        
        return Inertia::render('Users/Index', [
            'users' => $users,
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
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Użytkownik usunięty pomyślnie.');
    }
}
