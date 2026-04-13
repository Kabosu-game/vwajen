<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        try {
            if (!Schema::hasTable('roles')) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }
}
