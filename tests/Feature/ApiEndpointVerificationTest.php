<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiEndpointVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $managerUser;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $managerRole = Role::create(['name' => 'manager', 'description' => 'Manager']);
        $staffRole = Role::create(['name' => 'staff', 'description' => 'Staff']);

        $this->seed(PermissionSeeder::class);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@inventory.com',
            'role_id' => $adminRole->id,
        ]);

        $this->managerUser = User::factory()->create([
            'email' => 'manager@inventory.com',
            'role_id' => $managerRole->id,
        ]);

        $this->staffUser = User::factory()->create([
            'email' => 'staff@inventory.com',
            'role_id' => $staffRole->id,
        ]);
    }

    /** @test */
    public function test_login_endpoint_works()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@inventory.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token'
                ]
            ]);
    }

    /** @test */
    public function test_products_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }

    /** @test */
    public function test_products_endpoint_works_with_authentication()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    /** @test */
    public function test_staff_cannot_create_products_via_api()
    {
        $this->actingAs($this->staffUser)
            ->postJson('/api/products', [
                'name' => 'Blocked Chair',
                'sku' => 'BLK-001',
                'category_id' => 1,
                'cost_price' => 100,
                'selling_price' => 200,
                'current_stock' => 1,
                'reorder_level' => 0,
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function test_suppliers_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/suppliers')
            ->assertStatus(200);
    }

    /** @test */
    public function test_customers_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/customers')
            ->assertStatus(200);
    }

    /** @test */
    public function test_purchases_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/purchases')
            ->assertStatus(200);
    }

    /** @test */
    public function test_sales_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/sales')
            ->assertStatus(200);
    }

    /** @test */
    public function test_stock_adjustments_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/stock-adjustments')
            ->assertStatus(200);
    }

    /** @test */
    public function test_dashboard_report_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/reports/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.stats.week_sales', 0)
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'today_sales',
                        'week_sales',
                        'today_by_payment' => ['cash', 'mpesa', 'bank', 'card'],
                    ],
                ],
            ]);
    }

    /** @test */
    public function test_staff_cannot_open_accountant_reports()
    {
        $this->actingAs($this->staffUser)
            ->getJson('/api/reports/sales')
            ->assertStatus(403);
    }

    /** @test */
    public function test_sales_report_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/reports/sales')
            ->assertStatus(200);
    }

    /** @test */
    public function test_purchases_report_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/reports/purchases')
            ->assertStatus(200);
    }

    /** @test */
    public function test_stock_levels_report_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/reports/stock-levels')
            ->assertStatus(200);
    }

    /** @test */
    public function test_inventory_valuation_report_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/reports/inventory-valuation')
            ->assertStatus(200);
    }

    /** @test */
    public function test_logout_endpoint_works()
    {
        $this->actingAs($this->adminUser)
            ->postJson('/api/logout')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
    }

    /** @test */
    public function test_csrf_token_is_available_in_web_routes()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $this->assertNotNull($response->headers->getCookies());
    }
}
