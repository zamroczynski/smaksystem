<?php

namespace Tests\Feature\Services;

use App\Models\VatRate;
use App\Services\VatRateService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VatRateServiceTest extends TestCase
{
    use RefreshDatabase;

    private VatRateService $vatRateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vatRateService = $this->app->make(VatRateService::class);
    }

    #[Test]
    public function it_can_get_paginated_vat_rates(): void
    {
        VatRate::factory()->count(5)->create();

        $result = $this->vatRateService->getPaginatedVatRates([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    #[Test]
    public function it_filters_by_name(): void
    {
        VatRate::factory()->create(['name' => 'Stawka Podstawowa 23%']);
        VatRate::factory()->create(['name' => 'Stawka Obniżona 8%']);
        VatRate::factory()->create(['name' => 'Stawka Zerowa']);

        $resultAll = $this->vatRateService->getPaginatedVatRates(['filter' => 'stawka o']);
        $this->assertEquals(3, $resultAll->total());

        $resultSpecific = $this->vatRateService->getPaginatedVatRates(['filter' => 'stawka podstawowa']);
        $this->assertEquals(1, $resultSpecific->total());
        $this->assertEquals('Stawka Podstawowa 23%', $resultSpecific->items()[0]['name']);
    }

    #[Test]
    public function it_sorts_by_name_rate_and_id(): void
    {
        VatRate::factory()->create(['name' => 'Z-Name', 'rate' => 5.00]);
        VatRate::factory()->create(['name' => 'A-Name', 'rate' => 23.00]);
        VatRate::factory()->create(['name' => 'M-Name', 'rate' => 8.00]);

        // Sort by name asc
        $resultName = $this->vatRateService->getPaginatedVatRates(['sort' => 'name', 'direction' => 'asc']);
        $this->assertEquals('A-Name', $resultName->items()[0]['name']);

        // Sort by rate desc
        $resultRate = $this->vatRateService->getPaginatedVatRates(['sort' => 'rate', 'direction' => 'desc']);
        $this->assertEquals(23.00, $resultRate->items()[0]['rate']);
    }

    #[Test]
    public function it_can_get_paginated_disabled_vat_rates(): void
    {
        VatRate::factory()->create(['name' => 'Aktywna']);
        VatRate::factory()->create(['name' => 'Nieaktywna', 'deleted_at' => now()]);

        // Should get only active by default
        $activeResult = $this->vatRateService->getPaginatedVatRates([]);
        $this->assertEquals(1, $activeResult->total());

        // Should get disabled when requested
        $disabledResult = $this->vatRateService->getPaginatedVatRates(['show_disabled' => true]);
        $this->assertEquals(1, $disabledResult->total());
        $this->assertEquals('Nieaktywna', $disabledResult->items()[0]['name']);
    }

    #[Test]
    public function it_returns_data_in_correct_format(): void
    {
        VatRate::factory()->create(['rate' => 8.50]);
        $result = $this->vatRateService->getPaginatedVatRates([]);
        $item = $result->items()[0];

        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('rate', $item);
        $this->assertArrayHasKey('deleted_at', $item);
        $this->assertCount(4, $item);
        $this->assertEquals('8.50', $item['rate']); // Eloquent cast returns a string
    }
}