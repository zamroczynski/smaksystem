<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia; 
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash; 
use Spatie\Permission\Models\Role; 



class UsersController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('roles') // Wczytaj relację 'roles'
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
    public function store(Request $request)
    {
        // Walidacja danych wejściowych
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login', // Login musi być unikalny
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')], // Email może być pusty, ale jeśli jest, to musi być unikalny i poprawny
            'password' => 'required|string|min:8', // Hasło wymagane i min. 8 znaków
            'role_name' => ['nullable', 'string', Rule::exists('roles', 'name')], 
        ]);

        // Utwórz nowego użytkownika
        $user = User::create([
            'name' => $request->name,
            'login' => $request->login,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Przypisz rolę, jeśli została wybrana
        if ($request->filled('role_name')) {
            $user->assignRole($request->role_name);
        }

        // Przekieruj z komunikatem sukcesu
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
    public function update(Request $request, User $user)
    {
        // Walidacja dla pól aktualizacji (nazwa, login, email)
        $validationRules = [
            'name' => 'required|string|max:255',
            'login' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'login')->ignore($user->id), // Login unikalny, ale ignoruj aktualnego użytkownika
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id), // Email unikalny, ale ignoruj aktualnego użytkownika
            ],
            'role_name' => ['nullable', 'string', Rule::exists('roles', 'name')], // Rola opcjonalna, ale musi istnieć
        ];

        // Dodaj walidację hasła tylko, jeśli pole 'password' jest wypełnione
        if ($request->filled('password')) {
            $validationRules['password'] = 'string|min:8|nullable'; // Opcjonalne hasło, min. 8 znaków
        }

        $request->validate($validationRules);

        // Aktualizuj podstawowe dane użytkownika
        $user->name = $request->name;
        $user->login = $request->login;
        $user->email = $request->email;

        // Zmień hasło tylko, jeśli zostało podane
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save(); // Zapisz zmiany

        // Zaktualizuj role użytkownika
        if ($request->filled('role_name')) {
            $user->syncRoles([$request->role_name]); // syncRoles zastępuje bieżące role nowymi
        } else {
            // Jeśli rola nie została wybrana w formularzu, usuń wszystkie role
            $user->syncRoles([]);
        }

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
