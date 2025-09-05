<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = $this->app->make(ProductService::class);
    }

    #[Test]
    public function it_can_get_paginated_products(): void
    {
        Product::factory()->count(5)->create();

        $result = $this->productService->getPaginatedProducts([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    #[Test]
    public function it_filters_by_product_name_sku_and_category_name(): void
    {
        $categoryA = Category::factory()->create(['name' => 'Kategoria-A-Wyjatkowa']);
        $categoryB = Category::factory()->create(['name' => 'Kategoria-B-Inna']);

        Product::factory()->create(['name' => 'Unikalna Nazwa Produktu', 'category_id' => $categoryB->id]);
        Product::factory()->create(['sku' => 'UNIKALNY-SKU-123', 'category_id' => $categoryB->id]);
        Product::factory()->create(['category_id' => $categoryA->id]);

        // Filter by product name
        $resultName = $this->productService->getPaginatedProducts(['filter' => 'unikalna nazwa']);
        $this->assertEquals(1, $resultName->total());
        $this->assertEquals('Unikalna Nazwa Produktu', $resultName->items()[0]['name']);

        // Filter by SKU
        $resultSku = $this->productService->getPaginatedProducts(['filter' => 'unikalny-sku']);
        $this->assertEquals(1, $resultSku->total());
        $this->assertEquals('UNIKALNY-SKU-123', $resultSku->items()[0]['sku']);

        // Filter by category name
        $resultCategory = $this->productService->getPaginatedProducts(['filter' => 'kategoria-a-wyjatkowa']);
        $this->assertEquals(1, $resultCategory->total());
        $this->assertEquals('Kategoria-A-Wyjatkowa', $resultCategory->items()[0]['category_name']);
    }

    #[Test]
    public function it_sorts_by_name_sku_and_category(): void
    {
        $categoryA = Category::factory()->create(['name' => 'A-Kategoria']);
        $categoryZ = Category::factory()->create(['name' => 'Z-Kategoria']);

        Product::factory()->create(['name' => 'Z-Produkt', 'sku' => 'C-SKU', 'category_id' => $categoryA->id]);
        Product::factory()->create(['name' => 'A-Produkt', 'sku' => 'B-SKU', 'category_id' => $categoryZ->id]);
        Product::factory()->create(['name' => 'M-Produkt', 'sku' => 'A-SKU', 'category_id' => $categoryA->id]);

        // Sort by product name asc
        $resultName = $this->productService->getPaginatedProducts(['sort' => 'name', 'direction' => 'asc']);
        $this->assertEquals('A-Produkt', $resultName->items()[0]['name']);

        // Sort by SKU asc
        $resultSku = $this->productService->getPaginatedProducts(['sort' => 'sku', 'direction' => 'asc']);
        $this->assertEquals('A-SKU', $resultSku->items()[0]['sku']);

        // Sort by category name asc
        $resultCategory = $this->productService->getPaginatedProducts(['sort' => 'category', 'direction' => 'asc']);
        $this->assertEquals('A-Kategoria', $resultCategory->items()[0]['category_name']);
    }

    #[Test]
    public function it_can_get_paginated_disabled_products(): void
    {
        Product::factory()->create(['name' => 'Aktywny']);
        Product::factory()->create(['name' => 'Nieaktywny', 'deleted_at' => now()]);

        $activeResult = $this->productService->getPaginatedProducts([]);
        $this->assertEquals(1, $activeResult->total());

        $disabledResult = $this->productService->getPaginatedProducts(['show_disabled' => true]);
        $this->assertEquals(1, $disabledResult->total());
        $this->assertEquals('Nieaktywny', $disabledResult->items()[0]['name']);
    }

    #[Test]
    public function it_returns_data_in_correct_format(): void
    {
        Product::factory()->create();
        $result = $this->productService->getPaginatedProducts([]);
        $item = $result->items()[0];

        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('sku', $item);
        $this->assertArrayHasKey('category_name', $item);
        $this->assertArrayHasKey('unit_symbol', $item);
        $this->assertArrayHasKey('selling_price', $item);
        $this->assertArrayHasKey('is_sellable', $item);
        $this->assertArrayHasKey('deleted_at', $item);
        $this->assertCount(8, $item);
    }
}
