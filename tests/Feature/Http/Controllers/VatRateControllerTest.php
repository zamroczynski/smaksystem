<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VatRateControllerTest extends TestCase
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
        $this->get(route('vat-rates.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function users_without_permission_are_forbidden(): void
    {
        $this->actingAs($this->regularUser);

        $vatRate = VatRate::factory()->create();

        $this->get(route('vat-rates.index'))->assertForbidden();
        $this->get(route('vat-rates.create'))->assertForbidden();
        $this->post(route('vat-rates.store'), ['name' => 'Test', 'rate' => 23])->assertForbidden();
        $this->get(route('vat-rates.edit', $vatRate))->assertForbidden();
        $this->put(route('vat-rates.update', $vatRate), ['name' => 'Test', 'rate' => 23])->assertForbidden();
        $this->delete(route('vat-rates.destroy', $vatRate))->assertForbidden();
    }

    #[Test]
    public function users_with_permission_can_access_pages(): void
    {
        $this->actingAs($this->adminUser);

        $vatRate = VatRate::factory()->create();

        $this->get(route('vat-rates.index'))->assertOk();
        $this->get(route('vat-rates.create'))->assertOk();
        $this->get(route('vat-rates.edit', $vatRate))->assertOk();
    }

    #[Test]
    public function it_can_create_a_vat_rate(): void
    {
        $this->actingAs($this->adminUser);

        $this->post(route('vat-rates.store'), ['name' => 'Podstawowa', 'rate' => 23.00])
            ->assertRedirect(route('vat-rates.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vat_rates', ['name' => 'Podstawowa', 'rate' => 23.00]);
    }

    #[Test]
    public function it_can_update_a_vat_rate(): void
    {
        $this->actingAs($this->adminUser);
        $vatRate = VatRate::factory()->create();

        $this->put(route('vat-rates.update', $vatRate), ['name' => 'Zaktualizowana', 'rate' => 8.50])
            ->assertRedirect(route('vat-rates.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vat_rates', ['id' => $vatRate->id, 'name' => 'Zaktualizowana', 'rate' => 8.50]);
    }

    #[Test]
    public function it_can_soft_delete_a_vat_rate(): void
    {
        $this->actingAs($this->adminUser);
        $vatRate = VatRate::factory()->create();

        $this->delete(route('vat-rates.destroy', $vatRate))
            ->assertRedirect(route('vat-rates.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('vat_rates', ['id' => $vatRate->id]);
    }

    #[Test]
    public function it_can_restore_a_vat_rate(): void
    {
        $this->actingAs($this->adminUser);
        $vatRate = VatRate::factory()->create(['deleted_at' => now()]);

        $this->post(route('vat-rates.restore', $vatRate))
            ->assertRedirect(route('vat-rates.index'))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('vat_rates', ['id' => $vatRate->id]);
    }

    public static function validationDataProvider(): array
    {
        return [
            'name is required' => ['name', '', 'name'],
            'name is too long' => ['name', str_repeat('a', 256), 'name'],
            'rate is required' => ['rate', '', 'rate'],
            'rate is not numeric' => ['rate', 'abc', 'rate'],
            'rate is less than 0' => ['rate', -1, 'rate'],
            'rate is greater than 99.99' => ['rate', 100, 'rate'],
        ];
    }

    #[Test]
    #[DataProvider('validationDataProvider')]
    public function it_fails_validation_for_invalid_data(string $field, mixed $value, string $errorKey): void
    {
        $this->actingAs($this->adminUser);

        $data = ['name' => 'Poprawna Nazwa', 'rate' => 23];
        $data[$field] = $value;

        $response = $this->post(route('vat-rates.store'), $data);

        $response->assertSessionHasErrors($errorKey);
    }

    #[Test]
    public function it_fails_validation_when_name_is_not_unique(): void
    {
        $this->actingAs($this->adminUser);
        $existingRate = VatRate::factory()->create();

        // Test on store
        $this->post(route('vat-rates.store'), ['name' => $existingRate->name, 'rate' => 8])
            ->assertSessionHasErrors('name');
    }
}