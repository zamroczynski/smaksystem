<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::create(['name' => 'Edycja Produktów']);
        $adminRole = Role::create(['name' => 'admin'])->givePermissionTo($permission);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function users_without_permission_are_forbidden(): void
    {
        $this->actingAs($this->regularUser);

        $category = Category::factory()->create();

        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('categories.create'))->assertForbidden();
        $this->post(route('categories.store'), ['name' => 'Test'])->assertForbidden();
        $this->get(route('categories.edit', $category))->assertForbidden();
        $this->put(route('categories.update', $category), ['name' => 'Test'])->assertForbidden();
        $this->delete(route('categories.destroy', $category))->assertForbidden();
    }

    #[Test]
    public function users_with_permission_can_access_pages(): void
    {
        $this->actingAs($this->adminUser);

        $category = Category::factory()->create();

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('categories.create'))->assertOk();
        $this->get(route('categories.edit', $category))->assertOk();
    }

    #[Test]
    public function it_can_create_a_category(): void
    {
        $this->actingAs($this->adminUser);

        $this->post(route('categories.store'), ['name' => 'Nowa Kategoria'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Nowa Kategoria']);
    }

    #[Test]
    public function it_can_update_a_category(): void
    {
        $this->actingAs($this->adminUser);
        $category = Category::factory()->create();

        $this->put(route('categories.update', $category), ['name' => 'Zaktualizowana Kategoria'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Zaktualizowana Kategoria']);
    }

    #[Test]
    public function it_can_soft_delete_a_category(): void
    {
        $this->actingAs($this->adminUser);
        $category = Category::factory()->create();

        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    #[Test]
    public function it_can_restore_a_category(): void
    {
        $this->actingAs($this->adminUser);
        $category = Category::factory()->create(['deleted_at' => now()]);

        $this->post(route('categories.restore', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
    }

    public static function validationDataProvider(): array
    {
        return [
            'name is required' => ['name', '', 'name'],
            'name is too long' => ['name', str_repeat('a', 256), 'name'],
        ];
    }

    #[Test]
    #[DataProvider('validationDataProvider')]
    public function it_fails_validation_for_invalid_data(string $field, mixed $value, string $errorKey): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('categories.store'), [$field => $value]);

        $response->assertSessionHasErrors($errorKey);
    }

    #[Test]
    public function it_fails_validation_when_name_is_not_unique(): void
    {
        $this->actingAs($this->adminUser);
        $existingCategory = Category::factory()->create();

        // Test on store
        $this->post(route('categories.store'), ['name' => $existingCategory->name])
            ->assertSessionHasErrors('name');

        // Test on update
        $anotherCategory = Category::factory()->create();
        $this->put(route('categories.update', $anotherCategory), ['name' => $existingCategory->name])
            ->assertSessionHasErrors('name');
    }
}
