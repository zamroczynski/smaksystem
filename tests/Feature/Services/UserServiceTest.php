<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    /**
     * Setting up the test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = $this->app->make(UserService::class);

        Role::create(['name' => 'kierownik', 'guard_name' => 'web']);
        Role::create(['name' => 'pracownik', 'guard_name' => 'web']);
    }

    #[Test]
    public function it_can_create_a_user_without_a_role(): void
    {
        $userData = [
            'name' => 'Jan Kowalski',
            'login' => 'jankowalski',
            'email' => 'jan@kowalski.pl',
            'password' => 'password123',
            'role_name' => null,
        ];

        $user = $this->userService->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['login' => 'jankowalski']);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertCount(0, $user->getRoleNames());
    }

    #[Test]
    public function it_can_create_a_user_with_a_role(): void
    {
        $userData = [
            'name' => 'Anna Nowak',
            'login' => 'annanowak',
            'email' => 'anna@nowak.pl',
            'password' => 'password123',
            'role_name' => 'pracownik',
        ];

        $user = $this->userService->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['login' => 'annanowak']);
        $this->assertTrue($user->hasRole('pracownik'));
    }

    #[Test]
    public function it_can_update_user_data_and_change_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('pracownik');

        $updateData = [
            'name' => 'Zmienione Imię',
            'login' => 'nowy_login',
            'email' => 'nowy@email.pl',
            'role_name' => 'kierownik',
        ];

        $this->userService->update($user, $updateData);
        $user->refresh();

        $this->assertEquals('Zmienione Imię', $user->name);
        $this->assertEquals('nowy_login', $user->login);
        $this->assertTrue($user->hasRole('kierownik'));
        $this->assertFalse($user->hasRole('pracownik'));
    }

    #[Test]
    public function it_can_update_user_password_if_provided(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => Hash::make('stare_haslo')]);

        $updateData = [
            'name' => $user->name,
            'login' => $user->login,
            'email' => $user->email,
            'password' => 'nowe_super_haslo',
            'role_name' => null,
        ];

        $this->userService->update($user, $updateData);

        $this->assertTrue(Hash::check('nowe_super_haslo', $user->fresh()->password));
        $this->assertFalse(Hash::check('stare_haslo', $user->fresh()->password));
    }

    #[Test]
    public function it_does_not_update_password_if_not_provided(): void
    {
        $oldPasswordHash = Hash::make('stare_haslo');
        /** @var User $user */
        $user = User::factory()->create(['password' => $oldPasswordHash]);

        $updateData = [
            'name' => 'Nowe Imię',
            'login' => $user->login,
            'email' => $user->email,
            'password' => '',
            'role_name' => null,
        ];

        $this->userService->update($user, $updateData);

        $this->assertEquals($oldPasswordHash, $user->fresh()->password);
    }

    #[Test]
    public function it_can_get_paginated_users_with_filtering_and_sorting(): void
    {
        User::factory()->create(['name' => 'Adam Nowak']);
        User::factory()->create(['name' => 'Zenon Kowalski']);
        User::factory()->create(['name' => 'Celina Zając']);

        $filteredUsers = $this->userService->getPaginatedUsers(['filter' => 'Zenon']);
        $this->assertInstanceOf(LengthAwarePaginator::class, $filteredUsers);
        $this->assertEquals(1, $filteredUsers->total());
        $this->assertEquals('Zenon Kowalski', $filteredUsers->items()[0]['name']);

        $sortedUsers = $this->userService->getPaginatedUsers(['sort' => 'name', 'direction' => 'desc']);
        $this->assertEquals('Zenon Kowalski', $sortedUsers->items()[0]['name']);
        $this->assertEquals('Adam Nowak', $sortedUsers->items()[2]['name']);
    }

    #[Test]
    public function it_can_get_paginated_disabled_users(): void
    {
        User::factory()->create(['name' => 'Aktywny Użytkownik']);
        User::factory()->create(['name' => 'Nieaktywny Użytkownik', 'deleted_at' => now()]);

        $activeUsers = $this->userService->getPaginatedUsers([]);
        $this->assertEquals(1, $activeUsers->total());

        $disabledUsers = $this->userService->getPaginatedUsers(['show_disabled' => true]);
        $this->assertEquals(1, $disabledUsers->total());
        $this->assertEquals('Nieaktywny Użytkownik', $disabledUsers->items()[0]['name']);
    }

    #[Test]
    public function it_can_search_for_users_for_combobox(): void
    {
        User::factory()->create(['name' => 'Jan Testowy']);
        User::factory()->create(['name' => 'Anna Testerka']);

        $results = $this->userService->searchForCombobox('Test');

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('value', $results[0]);
        $this->assertArrayHasKey('label', $results[0]);
        $this->assertEquals('Jan Testowy', $results[0]['label']);
    }
}
