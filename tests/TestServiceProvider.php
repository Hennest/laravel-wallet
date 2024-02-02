<?php

declare(strict_types=1);

namespace Hennest\Wallet\Tests;

use Illuminate\Support\ServiceProvider;

final class TestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom([dirname(__DIR__) . '/tests/Database/Migrations']);
    }
}
