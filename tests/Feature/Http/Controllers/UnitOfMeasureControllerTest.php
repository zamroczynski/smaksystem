<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnitOfMeasureControllerTest extends TestCase
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
        $this->get(route('unit-of-measures.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function users_without_permission_are_forbidden(): void
    {
        $this->actingAs($this->regularUser);

        $unit = UnitOfMeasure::factory()->create();

        $this->get(route('unit-of-measures.index'))->assertForbidden();
        $this->get(route('unit-of-measures.create'))->assertForbidden();
        $this->post(route('unit-of-measures.store'), ['name' => 'Test', 'symbol' => 'tst'])->assertForbidden();
        $this->get(route('unit-of-measures.edit', $unit))->assertForbidden();
        $this->put(route('unit-of-measures.update', $unit), ['name' => 'Test', 'symbol' => 'tst'])->assertForbidden();
        $this->delete(route('unit-of-measures.destroy', $unit))->assertForbidden();
    }

    #[Test]
    public function users_with_permission_can_access_pages(): void
    {
        $this->actingAs($this->adminUser);

        $unit = UnitOfMeasure::factory()->create();

        $this->get(route('unit-of-measures.index'))->assertOk();
        $this->get(route('unit-of-measures.create'))->assertOk();
        $this->get(route('unit-of-measures.edit', $unit))->assertOk();
    }

    #[Test]
    public function it_can_create_a_unit_of_measure(): void
    {
        $this->actingAs($this->adminUser);

        $this->post(route('unit-of-measures.store'), ['name' => 'Kilogram', 'symbol' => 'kg'])
            ->assertRedirect(route('unit-of-measures.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('unit_of_measures', ['name' => 'Kilogram', 'symbol' => 'kg']);
    }

    #[Test]
    public function it_can_update_a_unit_of_measure(): void
    {
        $this->actingAs($this->adminUser);
        $unit = UnitOfMeasure::factory()->create();

        $this->put(route('unit-of-measures.update', $unit), ['name' => 'Sztuka', 'symbol' => 'szt'])
            ->assertRedirect(route('unit-of-measures.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('unit_of_measures', ['id' => $unit->id, 'name' => 'Sztuka', 'symbol' => 'szt']);
    }

    #[Test]
    public function it_can_soft_delete_a_unit_of_measure(): void
    {
        $this->actingAs($this->adminUser);
        $unit = UnitOfMeasure::factory()->create();

        $this->delete(route('unit-of-measures.destroy', $unit))
            ->assertRedirect(route('unit-of-measures.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('unit_of_measures', ['id' => $unit->id]);
    }

    #[Test]
    public function it_can_restore_a_unit_of_measure(): void
    {
        $this->actingAs($this->adminUser);
        $unit = UnitOfMeasure::factory()->create(['deleted_at' => now()]);

        $this->post(route('unit-of-measures.restore', $unit))
            ->assertRedirect(route('unit-of-measures.index'))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('unit_of_measures', ['id' => $unit->id]);
    }

    public static function validationDataProvider(): array
    {
        return [
            'name is required' => ['name', '', 'name'],
            'name is too long' => ['name', str_repeat('a', 256), 'name'],
            'symbol is required' => ['symbol', '', 'symbol'],
            'symbol is too long' => ['symbol', str_repeat('a', 11), 'symbol'],
        ];
    }

    #[Test]
    #[DataProvider('validationDataProvider')]
    public function it_fails_validation_for_invalid_data(string $field, mixed $value, string $errorKey): void
    {
        $this->actingAs($this->adminUser);

        $data = ['name' => 'Poprawna Nazwa', 'symbol' => 'psn'];
        $data[$field] = $value;

        $response = $this->post(route('unit-of-measures.store'), $data);

        $response->assertSessionHasErrors($errorKey);
    }

    #[Test]
    public function it_fails_validation_when_name_or_symbol_is_not_unique(): void
    {
        $this->actingAs($this->adminUser);
        $existingUnit = UnitOfMeasure::factory()->create();

        // Test name uniqueness on store
        $this->post(route('unit-of-measures.store'), ['name' => $existingUnit->name, 'symbol' => 'new'])
            ->assertSessionHasErrors('name');

        // Test symbol uniqueness on store
        $this->post(route('unit-of-measures.store'), ['name' => 'New Name', 'symbol' => $existingUnit->symbol])
            ->assertSessionHasErrors('symbol');
    }
}