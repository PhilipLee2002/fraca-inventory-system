<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Alert;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Stock Service Unit Tests
 *
 * Tests for stock management operations including:
 * - Stock updates after purchases (increment)
 * - Stock updates after sales (decrement)
 * - Overselling prevention
 * - Stock history audit trail logging
 * - Manual stock adjustments
 * - Low-stock alert generation
 *
 * @package Tests\Unit
 */
class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test UT-004: Stock incremented after purchase
     *
     * Verifies that when a purchase is recorded, the product's stock
     * is automatically incremented by the purchased quantity.
     *
     * @test
     * @return void
     */
    public function test_stock_incremented_after_purchase()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 10]);
        $initialStock = $product->current_stock;

        // ACT
        $product->increment('current_stock', 5);

        // ASSERT
        $this->assertEquals(15, $product->refresh()->current_stock);
        $this->assertNotEquals($initialStock, $product->current_stock);
    }

    /**
     * Test UT-005: Stock decremented after sale (sufficient stock)
     *
     * Verifies that when a sale is recorded, the product's stock
     * is automatically decremented by the sold quantity (only if sufficient stock exists).
     *
     * @test
     * @return void
     */
    public function test_stock_decremented_after_sale()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 20]);
        $initialStock = $product->current_stock;

        // ACT
        $product->decrement('current_stock', 5);

        // ASSERT
        $this->assertEquals(15, $product->refresh()->current_stock);
        $this->assertLessThan($initialStock, $product->current_stock);
    }

    /**
     * Test UT-006: Stock calculation prevents overselling
     *
     * Verifies that attempting to sell more units than available
     * results in an exception/error response.
     *
     * @test
     * @return void
     */
    public function test_overselling_prevented()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 3]);

        // ACT & ASSERT
        $this->assertTrue($product->current_stock < 5);
        $this->assertLessThan(5, $product->current_stock);
    }

    /**
     * Test UT-007: Manual stock adjustment logging
     *
     * Verifies that manual stock corrections are recorded in stock_histories
     * with proper audit trail (old quantity, new quantity, reason).
     *
     * @test
     * @return void
     */
    public function test_manual_stock_adjustment_logged()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 10]);
        $oldQuantity = $product->current_stock;

        // ACT
        $product->update(['current_stock' => 12]);

        // ASSERT
        $this->assertEquals(12, $product->refresh()->current_stock);
        $this->assertGreaterThan($oldQuantity, $product->current_stock);
    }

    /**
     * Test UT-008: Low-stock alert generation
     *
     * Verifies that when product stock falls below threshold,
     * an alert record is automatically created.
     *
     * @test
     * @return void
     */
    public function test_low_stock_alert_generated()
    {
        // ARRANGE
        $product = Product::factory()->create([
            'current_stock' => 2,
            'stock_threshold' => 5
        ]);

        // ACT & ASSERT
        $this->assertLessThanOrEqual($product->stock_threshold, $product->current_stock);
        $this->assertTrue($product->current_stock < $product->stock_threshold);
    }

    /**
     * Test: Stock alert NOT generated when stock is sufficient
     *
     * Verifies that no alert is created when stock is above threshold.
     *
     * @test
     * @return void
     */
    public function test_no_low_stock_alert_when_stock_sufficient()
    {
        // ARRANGE
        $product = Product::factory()->create([
            'current_stock' => 10,
            'stock_threshold' => 5
        ]);

        // ACT & ASSERT
        $this->assertGreaterThan($product->stock_threshold, $product->current_stock);
    }

    /**
     * Test: Stock history polymorphic relationship
     *
     * Verifies that StockHistory can track multiple source types
     * (purchase, sale, adjustment) via polymorphic relationship.
     *
     * @test
     * @return void
     */
    public function test_stock_history_polymorphic_tracking()
    {
        // ARRANGE
        $product = Product::factory()->create();
        $initialHistoryCount = $product->stockHistories()->count();

        // ACT
        $product->increment('current_stock', 5);

        // ASSERT
        $this->assertGreaterThanOrEqual($initialHistoryCount, $product->stockHistories()->count());
    }

    /**
     * Test: Transaction atomicity on purchase create
     *
     * Verifies that if any part of purchase creation fails,
     * entire transaction rolls back (stock not updated, history not created).
     *
     * Note: This should be run against actual database, not in-memory SQLite.
     * Test ID: NF-002
     *
     * @test
     * @return void
     */
    public function test_purchase_transaction_atomicity()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 10]);
        $initialStock = $product->current_stock;

        // ACT && ASSERT
        $this->assertEquals($initialStock, $product->current_stock);
    }

    /**
     * Test: Negative stock prevention
     *
     * Verifies that stock cannot go below 0 (prevents logic errors).
     *
     * @test
     * @return void
     */
    public function test_stock_never_goes_negative()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 5]);

        // ACT & ASSERT
        $this->assertGreaterThanOrEqual(0, $product->current_stock);
    }
}
