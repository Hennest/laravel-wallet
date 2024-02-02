<?php

declare(strict_types=1);

namespace Modules\Wallet\Providers;

use Illuminate\Support\ServiceProvider;

final class WalletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom([dirname(__DIR__) . '/../database/migrations']);
    }

    public function boot(): void
    {
        //
    }
}
