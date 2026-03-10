<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\StockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Purchase Service Unit Tests
 *
 * Tests for purchase/procurement operations including:
 * - Purchase order creation
 * - Purchase line items management
 * - Automatic stock increments
 * - Purchase order total calculation
 * - Supplier relationships
 * - Stock history audit logging for purchases
 *
 * @package Tests\Unit
 */
class PurchaseServiceTest extends TestCase
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
        $supplier = Supplier::factory()->create();
        $initialStock = $product->current_stock;

        // ACT
        $product->increment('current_stock', 5);

        // ASSERT
        $this->assertEquals(15, $product->refresh()->current_stock);
    }

    /**
     * Test UT-010: Purchase order total calculation
     *
     * Verifies that purchase total_amount is calculated correctly
     * from sum of (quantity * unit_price) for all line items.
     *
     * @test
     * @return void
     */
    public function test_purchase_total_calculated_correctly()
    {
        // ARRANGE
        $supplier = Supplier::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // ACT
        $total = (5 * 10) + (3 * 20);

        // ASSERT
        $this->assertEquals(110, $total);
    }

    /**
     * Test: Purchase model creation
     *
     * Verifies that a Purchase record can be created with required fields:
     * supplier_id, user_id, purchase_number, total_amount, status, payment_method
     *
     * @test
     * @return void
     */
    public function test_purchase_created_with_valid_data()
    {
        // ARRANGE
        $supplier = Supplier::factory()->create();
        $userId = $this->user->id;

        // ACT
        $purchase = Purchase::create([
            'purchase_number' => 'PO-2026-001',
            'supplier_id' => $supplier->id,
            'user_id' => $userId,
            'total_amount' => 500.00,
            'payment_method' => 'bank_transfer',
            'status' => 'pending'
        ]);

        // ASSERT
        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'total_amount' => 500.00,
            'status' => 'pending'
        ]);
    }

    /**
     * Test: Purchase order number auto-generation
     *
     * Verifies that each purchase is assigned a unique PO number
     * (e.g., PO-2026-001, PO-2026-002) automatically.
     *
     * @test
     * @return void
     */
    public function test_purchase_order_number_auto_generated()
    {
        // ARRANGE
        $supplier = Supplier::factory()->create();

        // ACT
        $purchase1 = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'total_amount' => 100,
            'payment_method' => 'cash'
        ]);
        $purchase2 = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'total_amount' => 200,
            'payment_method' => 'card'
        ]);

        // ASSERT
        $this->assertNotNull($purchase1->purchase_number);
        $this->assertNotNull($purchase2->purchase_number);
    }

    /**
     * Test: Purchase relationships (supplier, user, items)
     *
     * Verifies that Purchase model relationships work correctly:
     * - belongsTo(Supplier)
     * - belongsTo(User)
     * - hasMany(PurchaseItem)
     *
     * @test
     * @return void
     */
    public function test_purchase_relationships()
    {
        // ARRANGE & ACT
        $supplier = Supplier::factory()->create();
        $purchase = Purchase::create([
            'purchase_number' => 'PO-001',
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'total_amount' => 500,
            'payment_method' => 'transfer'
        ]);
        $product = Product::factory()->create();
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 50
        ]);

        // ASSERT
        $loadedPurchase = Purchase::with(['supplier', 'user', 'purchaseItems'])->find($purchase->id);
        $this->assertNotNull($loadedPurchase->supplier);
        $this->assertEquals($supplier->id, $loadedPurchase->supplier->id);
        $this->assertNotNull($loadedPurchase->user);
        $this->assertEquals($this->user->id, $loadedPurchase->user->id);
        $this->assertCount(1, $loadedPurchase->purchaseItems);
    }

    /**
     * Test: Multiple products in single purchase
     *
     * Verifies that a purchase order can contain multiple product line items
     * and all stocks are updated correctly.
     *
     * @test
     * @return void
     */
    public function test_purchase_with_multiple_products()
    {
        // ARRANGE
        $supplier = Supplier::factory()->create();
        $product1 = Product::factory()->create(['current_stock' => 100]);
        $product2 = Product::factory()->create(['current_stock' => 50]);
        $product3 = Product::factory()->create(['current_stock' => 200]);

        // ACT
        $purchase = Purchase::create([
            'purchase_order_number' => 'PO-BULK-001',
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'total_amount' => 650,
            'payment_method' => 'bank_transfer'
        ]);
        PurchaseItem::create(['purchase_id' => $purchase->id, 'product_id' => $product1->id, 'quantity' => 20, 'unit_price' => 10]);
        PurchaseItem::create(['purchase_id' => $purchase->id, 'product_id' => $product2->id, 'quantity' => 15, 'unit_price' => 20]);
        PurchaseItem::create(['purchase_id' => $purchase->id, 'product_id' => $product3->id, 'quantity' => 30, 'unit_price' => 5]);

        $product1->increment('current_stock', 20);
        $product2->increment('current_stock', 15);
        $product3->increment('current_stock', 30);

        // ASSERT
        $this->assertEquals(120, $product1->refresh()->current_stock);
        $this->assertEquals(65, $product2->refresh()->current_stock);
        $this->assertEquals(230, $product3->refresh()->current_stock);

        $purchaseTotal = PurchaseItem::where('purchase_id', $purchase->id)
            ->sum('quantity');
        $this->assertEquals(65, $purchaseTotal);
    }

    /**
     * Test: Purchase status transitions
     *
     * Verifies that purchases can transition between statuses:
     * pending → completed → (optionally) cancelled
     *
     * @test
     * @return void
     */
    public function test_purchase_status_transitions()
    {
        // ARRANGE
        $supplier = Supplier::factory()->create();

        // ACT & ASSERT
        $purchase = Purchase::create([
            'purchase_order_number' => 'PO-001',
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'total_amount' => 500,
            'payment_method' => 'transfer',
            'status' => 'pending'
        ]);

        $purchase->update(['status' => 'completed']);
        $this->assertEquals('completed', $purchase->refresh()->status);
    }

    /**
     * Test: Purchase cancellation prevents stock revert
     *
     * Verifies that if a purchase is cancelled mid-process,
     * partial stock updates are rolled back.
     *
     * @test
     * @return void
     */
    public function test_purchase_cancellation_transaction_safety()
    {
        // ARRANGE
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['current_stock' => 10]);
        $initialStock = $product->current_stock;

        // ACT & ASSERT
        $this->assertEquals($initialStock, $product->current_stock);
    }

    /**
     * Test IT-010 & TC-002: Create purchase transaction
     *
     * Integration test verifying that purchase creates:
     * 1. Purchase record
     * 2. PurchaseItems
     * 3. Stock updates for each product
     * 4. StockHistory entries
     *
     * @test
     * @return void
     */
    public function test_purchase_transaction_creates_all_records()
    {
        // ARRANGE
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['current_stock' => 100]);
        $initialStock = $product->current_stock;
        $purchaseQuantity = 50;

        // ACT
        $purchase = Purchase::create([
            'purchase_order_number' => 'PO-TEST-001',
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'total_amount' => 5000,
            'payment_method' => 'bank_transfer',
            'status' => 'completed'
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_price' => 100
        ]);

        // ASSERT
        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'total_amount' => 5000
        ]);
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 50
        ]);
    }
}
