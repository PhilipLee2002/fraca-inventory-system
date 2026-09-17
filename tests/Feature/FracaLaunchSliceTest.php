<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FracaLaunchSliceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesTableSeeder::class);
        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('name', 'admin')->value('id'),
        ]);
        $this->staff = User::factory()->create([
            'role_id' => Role::where('name', 'staff')->value('id'),
        ]);
    }

    public function test_named_fraca_users_are_seeded_and_demo_accounts_are_inactive(): void
    {
        User::factory()->create([
            'email' => 'admin@inventory.com',
            'status' => 'active',
            'role_id' => Role::where('name', 'admin')->value('id'),
        ]);

        $this->seed(UsersTableSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'benjamin@fracaservcomltd.co.ke',
            'name' => 'Benjamin Shitsukane',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'anne@fracaservcomltd.co.ke',
            'name' => 'Anne Jerubet',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'franklin@fracaservcomltd.co.ke',
            'name' => 'Franklin Shitsukane',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'reception@fracaservcomltd.co.ke',
            'name' => 'Receptionist',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@inventory.com',
            'status' => 'inactive',
        ]);
    }

    public function test_staff_do_not_receive_cost_price_on_products(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 1500,
            'selling_price' => 4000,
            'current_stock' => 4,
        ]);

        $this->actingAs($this->staff)
            ->getJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonMissingPath('data.cost_price')
            ->assertJsonPath('data.selling_price', '4000.00');

        $this->actingAs($this->admin)
            ->getJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.cost_price', '1500.00');
    }

    public function test_pending_sale_does_not_drop_stock(): void
    {
        $product = Product::factory()->create(['current_stock' => 10, 'selling_price' => 100]);

        $this->actingAs($this->staff)
            ->postJson('/api/sales', [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'status' => 'pending',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                ]],
            ])
            ->assertCreated();

        $this->assertSame(10, $product->fresh()->current_stock);
    }

    public function test_completed_sale_drops_stock_and_mpesa_requires_reference(): void
    {
        $product = Product::factory()->create(['current_stock' => 10, 'selling_price' => 100]);

        $this->actingAs($this->staff)
            ->postJson('/api/sales', [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'transfer',
                'status' => 'completed',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => 100,
                ]],
            ])
            ->assertStatus(422);

        $this->actingAs($this->staff)
            ->postJson('/api/sales', [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'transfer',
                'reference_number' => 'TILL-88421',
                'status' => 'completed',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => 100,
                ]],
            ])
            ->assertCreated();

        $this->assertSame(7, $product->fresh()->current_stock);
    }

    public function test_completing_a_pending_sale_drops_stock(): void
    {
        $product = Product::factory()->create(['current_stock' => 5, 'selling_price' => 50]);

        $create = $this->actingAs($this->admin)
            ->postJson('/api/sales', [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'status' => 'pending',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 50,
                ]],
            ])
            ->assertCreated();

        $saleId = $create->json('data.id');
        $this->assertSame(5, $product->fresh()->current_stock);

        $this->actingAs($this->admin)
            ->putJson('/api/sales/'.$saleId.'/status', ['status' => 'completed'])
            ->assertOk();

        $this->assertSame(3, $product->fresh()->current_stock);
    }

    public function test_dashboard_breaks_down_today_sales_by_payment_method(): void
    {
        $product = Product::factory()->create(['current_stock' => 20, 'selling_price' => 100]);

        $this->actingAs($this->admin)
            ->postJson('/api/sales', [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'status' => 'completed',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 200,
                ]],
            ])
            ->assertCreated();

        $this->actingAs($this->admin)
            ->getJson('/api/reports/dashboard')
            ->assertOk()
            ->assertJsonPath('data.stats.today_by_payment.cash', 200)
            ->assertJsonPath('data.stats.today_sales', 200);
    }

    public function test_printable_a4_invoice_streams_pdf(): void
    {
        $product = Product::factory()->create(['current_stock' => 8, 'selling_price' => 100]);

        $create = $this->actingAs($this->admin)
            ->postJson('/api/sales', [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'status' => 'completed',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 100,
                ]],
            ])
            ->assertCreated();

        $sale = Sale::find($create->json('data.id'));

        $response = $this->actingAs($this->admin)
            ->get(route('sales.invoice', $sale));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_inactive_demo_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@inventory.com',
            'status' => 'inactive',
            'role_id' => Role::where('name', 'admin')->value('id'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
