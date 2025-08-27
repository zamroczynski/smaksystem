<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        $options = [
            'show_disabled' => $request->boolean('show_disabled'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
        ];
        $users = $this->userService->getPaginatedUsers($options);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'flash' => session('flash'),
            'show_disabled' => $request->boolean('show_disabled'),
            'breadcrumbs' => $this->getUsersBreadcrumbs(),
            'filter' => $request->input('filter'),
            'sort_by' => $request->input('sort', 'id'),
            'sort_direction' => $request->input('direction', 'asc'),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(['id', 'name']),
            'breadcrumbs' => $this->getUsersBreadcrumbs('Dodaj pracownika', route('users.create')),
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
        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'login' => $user->login,
                'email' => $user->email,
                'current_role' => $user->getRoleNames()->first(),
            ],
            'roles' => Role::all(['id', 'name']),
            'breadcrumbs' => $this->getUsersBreadcrumbs('Edytuj pracownika', route('users.edit', $user)),
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

    /**
     * Generuje breadcrumbs dla zarządzania użytkownikami.
     */
    protected function getUsersBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Pracownikami', route('users.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
