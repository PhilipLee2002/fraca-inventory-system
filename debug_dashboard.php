<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
\Illuminate\Support\Facades\DB::reconnect();
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

// Create test data
$role = \App\Models\Role::create(['name' => 'admin', 'description' => 'Admin']);
\App\Models\User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password'), 'role_id' => $role->id]);

try {
    $controller = new \App\Http\Controllers\Api\ReportController();
    $response = $controller->dashboard();
    $data = json_decode($response->getContent(), true);
    echo "Status: " . $response->getStatusCode() . "\n";
    if (!$data['success']) {
        echo "Error: " . $data['message'] . "\n";
    } else {
        echo "Success!\n";
    }
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
}
