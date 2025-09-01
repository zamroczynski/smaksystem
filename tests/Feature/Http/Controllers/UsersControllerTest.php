<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::create(['name' => 'Edycja pracowników']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permission);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function guests_are_redirected_to_login_when_accessing_users_index(): void
    {
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_users_without_permission_are_forbidden(): void
    {
        $this->actingAs($this->regularUser);

        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();
        $this->get(route('users.edit', $this->adminUser))->assertForbidden();
    }

    #[Test]
    public function authenticated_users_with_permission_can_access_user_pages(): void
    {
        $this->actingAs($this->adminUser);

        $this->get(route('users.index'))->assertOk();
        $this->get(route('users.create'))->assertOk();
        $this->get(route('users.edit', $this->regularUser))->assertOk();
    }

    #[Test]
    public function admin_user_can_create_a_new_user(): void
    {
        $this->actingAs($this->adminUser);

        $userData = [
            'name' => 'Nowy Pracownik',
            'login' => 'nowypracownik',
            'password' => 'password123',
            'role_name' => null,
        ];

        $this->post(route('users.store'), $userData)
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['login' => 'nowypracownik']);
    }

    #[Test]
    public function regular_user_cannot_create_a_new_user(): void
    {
        $this->actingAs($this->regularUser);

        $userData = [
            'name' => 'Nieautoryzowany Pracownik',
            'login' => 'hacker',
            'password' => 'password123',
            'role_name' => null,
        ];

        $this->post(route('users.store'), $userData)
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['login' => 'hacker']);
    }

    /**
     * This method is the data provider for the validation test.
     * Structure: [field, invalid_value, error_key (optional)]
     */
    public static function validationDataProvider(): array
    {
        return [
            'name is required' => ['name', '', 'name'],
            'login is required' => ['login', '', 'login'],
            'login is too short' => ['login', 'ab', 'login'],
            'password is required' => ['password', '', 'password'],
            'password is too short' => ['password', '1234567', 'password'],
            'email must be a valid email' => ['email', 'to-nie-jest-email', 'email'],
            'role_name must exist' => ['role_name', 'nieistniejaca-rola', 'role_name'],
        ];
    }

    #[Test]
    #[DataProvider('validationDataProvider')]
    public function it_fails_validation_for_invalid_data_on_store(string $field, mixed $value, string $errorKey): void
    {
        $this->actingAs($this->adminUser);

        $userData = [
            'name' => 'Poprawna Nazwa',
            'login' => 'poprawny_login',
            'password' => 'poprawne_haslo_123',
        ];

        $userData[$field] = $value;

        $this->post(route('users.store'), $userData)
            ->assertSessionHasErrors($errorKey);
    }

    #[Test]
    public function it_fails_validation_when_login_or_email_is_not_unique(): void
    {
        $existingUser = $this->adminUser;

        $responseWithExistingLogin = $this->actingAs($existingUser)->post(route('users.store'), [
            'name' => 'Inny Użytkownik',
            'login' => $existingUser->login,
            'password' => 'password123',
        ]);
        $responseWithExistingLogin->assertSessionHasErrors('login');

        $responseWithExistingEmail = $this->actingAs($existingUser)->post(route('users.store'), [
            'name' => 'Jeszcze Inny Użytkownik',
            'login' => 'unikalnyLogin123',
            'email' => $existingUser->email,
            'password' => 'password123',
        ]);
        $responseWithExistingEmail->assertSessionHasErrors('email');
    }

    #[Test]
    public function it_allows_an_admin_to_soft_delete_another_user(): void
    {
        $this->actingAs($this->adminUser);

        $userToDelete = $this->regularUser;

        $this->delete(route('users.destroy', $userToDelete))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id,
        ]);
    }

    #[Test]
    public function it_prevents_a_user_from_deleting_themselves(): void
    {
        $this->actingAs($this->adminUser);

        $this->delete(route('users.destroy', $this->adminUser))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Nie możesz usunąć samego siebie!');

        $this->assertNotSoftDeleted('users', [
            'id' => $this->adminUser->id,
        ]);
    }

    // #[Test]
    // public function it_prevents_deleting_the_last_active_admin(): void
    // {
    //     $secondAdmin = User::factory()->create();
    //     $secondAdmin->assignRole('admin');

    //     $secondAdmin->delete();

    //     $this->actingAs($this->adminUser)->delete(route('users.destroy', $this->regularUser));

    //     $superAdmin = User::factory()->create();
    //     $superAdmin->assignRole('admin');

    //     $this->actingAs($superAdmin);

    //     $this->assertEquals(1, User::role('admin')->count());

    //     $this->actingAs($this->adminUser);

    //     $adminToDelete = $this->adminUser;

    //     $superAdmin = User::factory()->create();
    //     $superAdmin->assignRole('admin');
    //     $this->actingAs($superAdmin);

    //     $superAdmin->delete();

    //     $role = Role::where('name', 'admin')->first();
    //     $role->update(['name' => 'Kierownik']);

    //     $managerUser = User::factory()->create();
    //     $managerUser->assignRole('Kierownik');

    //     $this->assertEquals(1, User::role('Kierownik')->count());

    //     $actingManager = User::factory()->create();
    //     $actingManager->assignRole('Kierownik');
    //     $this->actingAs($actingManager);

    //     $actingManager->delete();

    //     $actingManager->restore();
    //     $this->actingAs($actingManager);

    //     $this->delete(route('users.destroy', $managerUser))
    //          ->assertRedirect(route('users.index'))
    //          ->assertSessionHas('error', 'Nie możesz wyłączyć ostatniego aktywnego administratora!');

    //     $this->assertNotSoftDeleted('users', ['id' => $managerUser->id]);
    // }
}
