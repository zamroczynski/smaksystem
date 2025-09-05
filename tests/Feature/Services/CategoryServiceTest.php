<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = $this->app->make(CategoryService::class);
    }

    #[Test]
    public function it_can_get_paginated_categories(): void
    {
        Category::factory()->count(5)->create();

        $result = $this->categoryService->getPaginatedCategories([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    #[Test]
    public function it_filters_by_name(): void
    {
        Category::factory()->create(['name' => 'Przystawki']);
        Category::factory()->create(['name' => 'Przekąski']);
        Category::factory()->create(['name' => 'Dania Główne']);

        $result = $this->categoryService->getPaginatedCategories(['filter' => 'przys']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Przystawki', $result->items()[0]['name']);
    }

    #[Test]
    public function it_sorts_by_name_and_id(): void
    {
        Category::factory()->create(['name' => 'Zupy']);
        Category::factory()->create(['name' => 'Napoje']);
        Category::factory()->create(['name' => 'Desery']);

        // Sort by name ascending
        $resultAsc = $this->categoryService->getPaginatedCategories(['sort' => 'name', 'direction' => 'asc']);
        $this->assertEquals('Desery', $resultAsc->items()[0]['name']);

        // Sort by name descending
        $resultDesc = $this->categoryService->getPaginatedCategories(['sort' => 'name', 'direction' => 'desc']);
        $this->assertEquals('Zupy', $resultDesc->items()[0]['name']);
    }

    #[Test]
    public function it_can_get_paginated_disabled_categories(): void
    {
        Category::factory()->create(['name' => 'Aktywna Kategoria']);
        Category::factory()->create(['name' => 'Nieaktywna Kategoria', 'deleted_at' => now()]);

        // Should only get active by default
        $activeResult = $this->categoryService->getPaginatedCategories([]);
        $this->assertEquals(1, $activeResult->total());
        $this->assertEquals('Aktywna Kategoria', $activeResult->items()[0]['name']);

        // Should get disabled when requested
        $disabledResult = $this->categoryService->getPaginatedCategories(['show_disabled' => true]);
        $this->assertEquals(1, $disabledResult->total());
        $this->assertEquals('Nieaktywna Kategoria', $disabledResult->items()[0]['name']);
    }

    #[Test]
    public function it_returns_data_in_correct_format(): void
    {
        Category::factory()->create();
        $result = $this->categoryService->getPaginatedCategories([]);
        $item = $result->items()[0];

        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('deleted_at', $item);
        $this->assertCount(3, $item);
    }
}
