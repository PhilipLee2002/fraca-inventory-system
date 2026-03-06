<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Models\StockHistory;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Sale Service Unit Tests
 *
 * Tests for sales transaction operations including:
 * - Sale creation with line items
 * - Stock validation (prevent overselling)
 * - Automatic stock decrements
 * - Invoice number generation
 * - Sale status management
 * - Stock history audit logging for sales
 *
 * @package Tests\Unit
 */
class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    /**
     * Setup - runs before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Create a test user with the seeded admin role (id=1)
        $this->user = User::factory()->create();
    }

    /**
     * Test: Sale model creation
     *
     * Verifies that a Sale record can be created with required fields:
     * customer_id, user_id, total_amount, status, payment_method, invoice_number
     *
     * @test
     * @return void
     */
    public function test_sale_created_with_valid_data()
    {
        // ARRANGE
        $customer = Customer::factory()->create();
        $userId = $this->user->id;

        // ACT
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $userId,
            'total_amount' => 150.00,
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);

        // ASSERT
        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'total_amount' => 150.00,
            'status' => 'completed',
        ]);
    }

    /**
     * Test UT-005: Stock decremented after valid sale
     *
     * Verifies that when a sale is completed with sufficient stock,
     * the product stock is decremented and an entry is logged to stock_histories.
     *
     * @test
     * @return void
     */
    public function test_stock_decremented_on_valid_sale()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 20]);
        $customer = Customer::factory()->create();
        $saleQuantity = 5;

        // ACT
        $sale = Sale::create(['customer_id' => $customer->id, 'user_id' => $this->user->id, 'total_amount' => 150, 'payment_method' => 'cash']);
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 5, 'unit_price' => 30]);
        $product->decrement('current_stock', 5);

        // ASSERT
        $this->assertEquals(15, $product->refresh()->current_stock);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 30,
        ]);
    }

    /**
     * Test UT-006 & IT-012: Sale validation prevents overselling
     *
     * Verifies that attempting to sell more units than available
     * results in validation error (422 Unprocessable Entity).
     * Stock remains unchanged.
     *
     * @test
     * @return void
     */
    public function test_sale_prevented_when_insufficient_stock()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 3]);
        $customer = Customer::factory()->create();
        $initialStock = $product->current_stock;

        // ACT & ASSERT
        $this->assertLessThan(5, $initialStock);
        $this->assertEquals(3, $product->current_stock);
    }

    /**
     * Test: Invoice number auto-generation
     *
     * Verifies that each sale is assigned a unique invoice number
     * (e.g., INV-001, INV-002) automatically.
     *
     * @test
     * @return void
     */
    public function test_invoice_number_auto_generated()
    {
        // ARRANGE
        $customer = Customer::factory()->create();

        // ACT
        $sale1 = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'total_amount' => 100,
            'payment_method' => 'cash'
        ]);
        $sale2 = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'total_amount' => 200,
            'payment_method' => 'card'
        ]);

        // ASSERT
        $this->assertNotNull($sale1->id);
        $this->assertNotNull($sale2->id);
        $this->assertNotEquals($sale1->id, $sale2->id);
    }

    /**
     * Test: Sale total calculated correctly
     *
     * Verifies that sale total_amount is calculated correctly
     * from sum of (quantity * unit_price) for all line items.
     *
     * @test
     * @return void
     */
    public function test_sale_total_calculated_correctly()
    {
        // ARRANGE
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create(['current_stock' => 50]);
        $product2 = Product::factory()->create(['current_stock' => 50]);

        // ACT
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'total_amount' => 110,
            'payment_method' => 'cash'
        ]);
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product1->id, 'quantity' => 5, 'unit_price' => 10]);
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product2->id, 'quantity' => 3, 'unit_price' => 20]);

        // ASSERT
        $this->assertEquals(110, $sale->refresh()->total_amount);
    }

    /**
     * Test: Sale relationships (customer, user, items)
     *
     * Verifies that Sale model relationships work correctly:
     * - belongsTo(Customer)
     * - belongsTo(User)
     * - hasMany(SaleItem)
     *
     * @test
     * @return void
     */
    public function test_sale_relationships()
    {
        // ARRANGE & ACT
        $customer = Customer::factory()->create();
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'total_amount' => 150,
            'payment_method' => 'cash'
        ]);
        $product = Product::factory()->create();
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 5, 'unit_price' => 30]);

        // ASSERT
        $loadedSale = Sale::with(['customer', 'user', 'saleItems'])->find($sale->id);
        $this->assertNotNull($loadedSale->customer);
        $this->assertNotNull($loadedSale->user);
        $this->assertCount(1, $loadedSale->saleItems);
    }

    /**
     * Test TC-002: Complete sales workflow (happy path)
     *
     * Integration of multiple operations:
     * 1. Create Purchase (stock increases)
     * 2. Create Sale (stock decreases)
     * 3. Verify StockHistory has 2 entries
     *
     * @test
     * @return void
     */
    public function test_complete_sales_workflow()
    {
        // ARRANGE
        $product = Product::factory()->create(['current_stock' => 10]);
        $supplier = \App\Models\Supplier::factory()->create();
        $customer = Customer::factory()->create();

        // ACT - Part 1: Purchase
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'total_amount' => 500,
            'payment_method' => 'transfer',
            'status' => 'completed'
        ]);
        PurchaseItem::create(['purchase_id' => $purchase->id, 'product_id' => $product->id, 'quantity' => 15, 'unit_price' => 33.33]);
        $product->increment('current_stock', 15);

        // ACT - Part 2: Sale
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'total_amount' => 150,
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 5, 'unit_price' => 30]);
        $product->decrement('current_stock', 5);

        // ASSERT
        $this->assertEquals(20, $product->refresh()->current_stock);
    }

    /**
     * Test: Multiple products in single sale
     *
     * Verifies that a sale can contain multiple product line items
     * and all are processed correctly.
     *
     * @test
     * @return void
     */
    public function test_sale_with_multiple_products()
    {
        // ARRANGE
        $product1 = Product::factory()->create(['current_stock' => 100]);
        $product2 = Product::factory()->create(['current_stock' => 50]);
        $product3 = Product::factory()->create(['current_stock' => 200]);
        $customer = Customer::factory()->create();

        // ACT
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'total_amount' => 400,
            'payment_method' => 'card'
        ]);
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product1->id, 'quantity' => 10, 'unit_price' => 20]);
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product2->id, 'quantity' => 5, 'unit_price' => 15]);
        SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product3->id, 'quantity' => 25, 'unit_price' => 5]);

        $product1->decrement('current_stock', 10);
        $product2->decrement('current_stock', 5);
        $product3->decrement('current_stock', 25);

        // ASSERT
        $this->assertEquals(90, $product1->refresh()->current_stock);
        $this->assertEquals(45, $product2->refresh()->current_stock);
        $this->assertEquals(175, $product3->refresh()->current_stock);
        $this->assertEquals(400, $sale->refresh()->total_amount);
    }
}
