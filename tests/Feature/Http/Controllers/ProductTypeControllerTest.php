<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductTypeControllerTest extends TestCase
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
        $this->get(route('product-types.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function users_without_permission_are_forbidden(): void
    {
        $this->actingAs($this->regularUser);

        $productType = ProductType::factory()->create();

        $this->get(route('product-types.index'))->assertForbidden();
        $this->get(route('product-types.create'))->assertForbidden();
        $this->post(route('product-types.store'), ['name' => 'Test'])->assertForbidden();
        $this->get(route('product-types.edit', $productType))->assertForbidden();
        $this->put(route('product-types.update', $productType), ['name' => 'Test'])->assertForbidden();
        $this->delete(route('product-types.destroy', $productType))->assertForbidden();
    }

    #[Test]
    public function users_with_permission_can_access_pages(): void
    {
        $this->actingAs($this->adminUser);

        $productType = ProductType::factory()->create();

        $this->get(route('product-types.index'))->assertOk();
        $this->get(route('product-types.create'))->assertOk();
        $this->get(route('product-types.edit', $productType))->assertOk();
    }

    #[Test]
    public function it_can_create_a_product_type(): void
    {
        $this->actingAs($this->adminUser);

        $this->post(route('product-types.store'), ['name' => 'Nowy Typ'])
            ->assertRedirect(route('product-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('product_types', ['name' => 'Nowy Typ']);
    }

    #[Test]
    public function it_can_update_a_product_type(): void
    {
        $this->actingAs($this->adminUser);
        $productType = ProductType::factory()->create();

        $this->put(route('product-types.update', $productType), ['name' => 'Zaktualizowana Nazwa'])
            ->assertRedirect(route('product-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('product_types', ['id' => $productType->id, 'name' => 'Zaktualizowana Nazwa']);
    }

    #[Test]
    public function it_can_soft_delete_a_product_type(): void
    {
        $this->actingAs($this->adminUser);
        $productType = ProductType::factory()->create();

        $this->delete(route('product-types.destroy', $productType))
            ->assertRedirect(route('product-types.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('product_types', ['id' => $productType->id]);
    }

    #[Test]
    public function it_can_restore_a_product_type(): void
    {
        $this->actingAs($this->adminUser);
        $productType = ProductType::factory()->create(['deleted_at' => now()]);

        $this->post(route('product-types.restore', $productType))
            ->assertRedirect(route('product-types.index'))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('product_types', ['id' => $productType->id]);
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

        $response = $this->post(route('product-types.store'), [$field => $value]);

        $response->assertSessionHasErrors($errorKey);
    }

    #[Test]
    public function it_fails_validation_when_name_is_not_unique(): void
    {
        $this->actingAs($this->adminUser);
        $existingType = ProductType::factory()->create();

        // Test on store
        $this->post(route('product-types.store'), ['name' => $existingType->name])
            ->assertSessionHasErrors('name');

        // Test on update
        $anotherType = ProductType::factory()->create();
        $this->put(route('product-types.update', $anotherType), ['name' => $existingType->name])
            ->assertSessionHasErrors('name');
    }
}