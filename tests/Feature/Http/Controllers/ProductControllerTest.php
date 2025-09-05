<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;
    private array $relatedModels;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::create(['name' => 'Edycja Produktów']);
        $adminRole = Role::create(['name' => 'admin'])->givePermissionTo($permission);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        $this->regularUser = User::factory()->create();

        // Create related models once for all tests
        $this->relatedModels = [
            'product_type_id' => ProductType::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
            'unit_of_measure_id' => UnitOfMeasure::factory()->create()->id,
            'vat_rate_id' => VatRate::factory()->create()->id,
        ];
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function users_without_permission_are_forbidden(): void
    {
        $this->actingAs($this->regularUser);

        $product = Product::factory()->create();

        $this->get(route('products.index'))->assertForbidden();
        $this->get(route('products.create'))->assertForbidden();
        $this->post(route('products.store'), [])->assertForbidden();
        $this->get(route('products.edit', $product))->assertForbidden();
        $this->put(route('products.update', $product), [])->assertForbidden();
        $this->delete(route('products.destroy', $product))->assertForbidden();
    }

    #[Test]
    public function it_can_create_a_product(): void
    {
        $this->actingAs($this->adminUser);

        $productData = array_merge([
            'name' => 'Nowy Produkt',
            'is_sellable' => true,
            'is_inventoried' => true,
            'selling_price' => 123.45,
        ], $this->relatedModels);

        $this->post(route('products.store'), $productData)
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['name' => 'Nowy Produkt', 'selling_price' => 123.45]);
    }

    #[Test]
    public function it_can_update_a_product(): void
    {
        $this->actingAs($this->adminUser);
        $product = Product::factory()->create();

        $updateData = array_merge([
            'name' => 'Zaktualizowany Produkt',
            'sku' => 'UPD-SKU-123',
            'is_sellable' => false,
            'is_inventoried' => false,
        ], $this->relatedModels);

        $this->put(route('products.update', $product), $updateData)
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Zaktualizowany Produkt', 'sku' => 'UPD-SKU-123']);
    }

    #[Test]
    public function it_can_soft_delete_a_product(): void
    {
        $this->actingAs($this->adminUser);
        $product = Product::factory()->create();

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    #[Test]
    public function it_can_restore_a_product(): void
    {
        $this->actingAs($this->adminUser);
        $product = Product::factory()->create(['deleted_at' => now()]);

        $this->post(route('products.restore', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }

    public static function validationDataProvider(): array
    {
        return [
            'name is required' => ['name', '', 'name'],
            'product_type_id is required' => ['product_type_id', '', 'product_type_id'],
            'product_type_id must exist' => ['product_type_id', 999, 'product_type_id'],
            'category_id is required' => ['category_id', '', 'category_id'],
            'category_id must exist' => ['category_id', 999, 'category_id'],
            'is_sellable is required' => ['is_sellable', '', 'is_sellable'],
            'is_sellable must be boolean' => ['is_sellable', 'not-a-bool', 'is_sellable'],
            'selling_price is required if sellable' => ['selling_price', '', 'selling_price', ['is_sellable' => true]],
            'selling_price must be numeric' => ['selling_price', 'abc', 'selling_price', ['is_sellable' => true]],
        ];
    }

    #[Test]
    #[DataProvider('validationDataProvider')]
    public function it_fails_validation_for_invalid_data(string $field, mixed $value, string $errorKey, array $overrideData = []): void
    {
        $this->actingAs($this->adminUser);

        $productData = array_merge([
            'name' => 'Testowy Produkt',
            'is_sellable' => false,
            'is_inventoried' => true,
        ], $this->relatedModels, $overrideData);

        $productData[$field] = $value;

        $this->post(route('products.store'), $productData)
            ->assertSessionHasErrors($errorKey);
    }

    #[Test]
    public function it_fails_validation_when_sku_is_not_unique(): void
    {
        $this->actingAs($this->adminUser);
        $existingProduct = Product::factory()->create(['sku' => 'SKU-123']);

        $productData = array_merge([
            'name' => 'Inny Produkt',
            'sku' => 'SKU-123',
            'is_sellable' => false,
            'is_inventoried' => true,
        ], $this->relatedModels);

        $this->post(route('products.store'), $productData)
            ->assertSessionHasErrors('sku');
    }
}