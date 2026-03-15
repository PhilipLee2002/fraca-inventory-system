<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Register all permissions with Laravel's Gate so @can() works in Blade
        try {
            Permission::all()->each(function ($permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermission($permission->name);
                });
            });
        } catch (\Exception $e) {
            // Silently fail during migrations/before DB is ready
        }
    }
}
