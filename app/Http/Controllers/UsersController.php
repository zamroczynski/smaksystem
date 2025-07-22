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
        // return redirect()->route('users.index')->with('success', 'Użytkownik został pomyślnie dodany.');
        return to_route('users.index')
            ->with('success', 'Użytkownik został pomyślnie dodany.');
            // Inertia automatycznie wykryje to przekierowanie i odświeży stronę.
            // Domyślne przekierowanie Inertii jest wystarczające, by odświeżyć dane,
            // jeśli strona `/users` jest celem tego przekierowania.
            // Problem zazwyczaj leży w tym, że Inertia nie odświeża CAŁEJ aplikacji,
            // a jedynie komponent, więc czasem potrzebujemy czegoś więcej.
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'login' => 'required|string|max:255|unique:users,login,' . $user->id,
            // Dodaj walidację dla innych pól, które będziesz edytować, np. dla ról
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'login' => $request->login,
            // Zaktualizuj inne pola
        ]);

        // Jeśli używasz Spatie/laravel-permission do ról:
        // Pamiętaj, aby dodać import dla Spatie\Permission\Models\Role
        // $user->syncRoles($request->roles); // Zakładając, że $request->roles to tablica nazw ról

        return redirect()->route('users.index')->with('success', 'Użytkownik zaktualizowany pomyślnie.');
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
