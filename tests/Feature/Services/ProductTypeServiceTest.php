<?php

namespace Tests\Feature\Feature\Services;

use App\Models\ProductType;
use App\Services\ProductTypeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductTypeService $productTypeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productTypeService = $this->app->make(ProductTypeService::class);
    }

    #[Test]
    public function it_can_get_paginated_product_types(): void
    {
        ProductType::factory()->count(5)->create();

        $result = $this->productTypeService->getPaginatedProductTypes([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    #[Test]
    public function it_filters_by_name(): void
    {
        ProductType::factory()->create(['name' => 'Napoje Gorące']);
        ProductType::factory()->create(['name' => 'Napoje Zimne']);
        ProductType::factory()->create(['name' => 'Alkohole']);

        $result = $this->productTypeService->getPaginatedProductTypes(['filter' => 'napoje']);

        $this->assertEquals(2, $result->total());
        $this->assertStringContainsString('Napoje', $result->items()[0]['name']);
        $this->assertStringContainsString('Napoje', $result->items()[1]['name']);
    }

    #[Test]
    public function it_sorts_by_name_and_id(): void
    {
        ProductType::factory()->create(['name' => 'Zupy']);
        ProductType::factory()->create(['name' => 'Dania Główne']);
        ProductType::factory()->create(['name' => 'Desery']);

        // Sort by name ascending
        $resultAsc = $this->productTypeService->getPaginatedProductTypes(['sort' => 'name', 'direction' => 'asc']);
        $this->assertEquals('Dania Główne', $resultAsc->items()[0]['name']);

        // Sort by name descending
        $resultDesc = $this->productTypeService->getPaginatedProductTypes(['sort' => 'name', 'direction' => 'desc']);
        $this->assertEquals('Zupy', $resultDesc->items()[0]['name']);
    }

    #[Test]
    public function it_can_get_paginated_disabled_product_types(): void
    {
        ProductType::factory()->create(['name' => 'Aktywny Typ']);
        ProductType::factory()->create(['name' => 'Nieaktywny Typ', 'deleted_at' => now()]);

        $activeResult = $this->productTypeService->getPaginatedProductTypes([]);
        $this->assertEquals(1, $activeResult->total());
        $this->assertEquals('Aktywny Typ', $activeResult->items()[0]['name']);

        $disabledResult = $this->productTypeService->getPaginatedProductTypes(['show_disabled' => true]);
        $this->assertEquals(1, $disabledResult->total());
        $this->assertEquals('Nieaktywny Typ', $disabledResult->items()[0]['name']);
    }

    #[Test]
    public function it_returns_data_in_correct_format(): void
    {
        ProductType::factory()->create();
        $result = $this->productTypeService->getPaginatedProductTypes([]);
        $item = $result->items()[0];

        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('deleted_at', $item);
        $this->assertCount(3, $item);
    }
}
