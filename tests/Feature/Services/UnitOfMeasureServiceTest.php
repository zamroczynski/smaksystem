<?php

namespace Tests\Feature\Services;

use App\Models\UnitOfMeasure;
use App\Services\UnitOfMeasureService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitOfMeasureServiceTest extends TestCase
{
    use RefreshDatabase;

    private UnitOfMeasureService $unitOfMeasureService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->unitOfMeasureService = $this->app->make(UnitOfMeasureService::class);
    }

    #[Test]
    public function it_can_get_paginated_unit_of_measures(): void
    {
        UnitOfMeasure::factory()->count(5)->create();

        $result = $this->unitOfMeasureService->getPaginatedUnitOfMeasures([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    #[Test]
    public function it_filters_by_name_and_symbol(): void
    {
        UnitOfMeasure::factory()->create(['name' => 'Kilogram', 'symbol' => 'kg']);
        UnitOfMeasure::factory()->create(['name' => 'Sztuka', 'symbol' => 'szt']);
        UnitOfMeasure::factory()->create(['name' => 'Gram', 'symbol' => 'g']);

        // Filter by name
        $resultByName = $this->unitOfMeasureService->getPaginatedUnitOfMeasures(['filter' => 'gram']);
        $this->assertEquals(2, $resultByName->total()); // Finds Kilo'gram' and 'Gram'

        // Filter by symbol
        $resultBySymbol = $this->unitOfMeasureService->getPaginatedUnitOfMeasures(['filter' => 'szt']);
        $this->assertEquals(1, $resultBySymbol->total());
        $this->assertEquals('Sztuka', $resultBySymbol->items()[0]['name']);
    }

    #[Test]
    public function it_sorts_by_name_symbol_and_id(): void
    {
        UnitOfMeasure::factory()->create(['name' => 'Z-Name', 'symbol' => 'C-Symbol']);
        UnitOfMeasure::factory()->create(['name' => 'A-Name', 'symbol' => 'B-Symbol']);
        UnitOfMeasure::factory()->create(['name' => 'M-Name', 'symbol' => 'A-Symbol']);

        // Sort by name asc
        $resultName = $this->unitOfMeasureService->getPaginatedUnitOfMeasures(['sort' => 'name', 'direction' => 'asc']);
        $this->assertEquals('A-Name', $resultName->items()[0]['name']);

        // Sort by symbol asc
        $resultSymbol = $this->unitOfMeasureService->getPaginatedUnitOfMeasures(['sort' => 'symbol', 'direction' => 'asc']);
        $this->assertEquals('A-Symbol', $resultSymbol->items()[0]['symbol']);
    }

    #[Test]
    public function it_can_get_paginated_disabled_unit_of_measures(): void
    {
        UnitOfMeasure::factory()->create(['name' => 'Aktywna']);
        UnitOfMeasure::factory()->create(['name' => 'Nieaktywna', 'deleted_at' => now()]);

        // Should get only active by default
        $activeResult = $this->unitOfMeasureService->getPaginatedUnitOfMeasures([]);
        $this->assertEquals(1, $activeResult->total());

        // Should get disabled when requested
        $disabledResult = $this->unitOfMeasureService->getPaginatedUnitOfMeasures(['show_disabled' => true]);
        $this->assertEquals(1, $disabledResult->total());
        $this->assertEquals('Nieaktywna', $disabledResult->items()[0]['name']);
    }

    #[Test]
    public function it_returns_data_in_correct_format(): void
    {
        UnitOfMeasure::factory()->create();
        $result = $this->unitOfMeasureService->getPaginatedUnitOfMeasures([]);
        $item = $result->items()[0];

        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('symbol', $item);
        $this->assertArrayHasKey('deleted_at', $item);
        $this->assertCount(4, $item);
    }
}