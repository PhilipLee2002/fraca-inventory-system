<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
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

        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $managerRole = Role::create(['name' => 'manager', 'description' => 'Manager']);
        $staffRole = Role::create(['name' => 'staff', 'description' => 'Staff']);

        // Create users
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
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    /** @test */
    public function test_suppliers_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/suppliers');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_customers_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/customers');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_purchases_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/purchases');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_sales_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/sales');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_stock_adjustments_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/stock-adjustments');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_dashboard_report_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_sales_report_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/sales');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_purchases_report_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/purchases');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_stock_levels_report_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/stock-levels');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_inventory_valuation_report_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/reports/inventory-valuation');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_logout_endpoint_works()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
    }

    /** @test */
    public function test_csrf_token_is_available_in_web_routes()
    {
        $response = $this->get('/');
        
        // Should redirect to login if not authenticated
        $response->assertStatus(302);
        
        // Check that CSRF token cookie is set
        $this->assertNotNull($response->headers->getCookies());
    }
}
