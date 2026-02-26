<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Database\Seeders\RolesTableSeeder;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Disable foreign key constraints for SQLite tests BEFORE migrations run
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off');
        }

        parent::setUp();

        // Seed roles for tests that use RefreshDatabase
        if (method_exists($this, 'seed')) {
            $this->seed(RolesTableSeeder::class);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Re-enable foreign key constraints for SQLite tests
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=on');
        }
    }
}
