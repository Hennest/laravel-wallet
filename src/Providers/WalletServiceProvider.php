<?php

declare(strict_types=1);

namespace Hennest\Wallet\Providers;

use Illuminate\Support\ServiceProvider;

final class WalletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/wallet.php', 'wallet');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom([dirname(__DIR__) . '/../database/migrations']);

        $this->publishes([
            __DIR__ . '/../../config/wallet.php' => config_path('wallet.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'migrations');
    }
}
