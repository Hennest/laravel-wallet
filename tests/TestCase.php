<?php

declare(strict_types=1);

namespace Hennest\Wallet\Tests;

use Hennest\Math\Providers\MathServiceProvider;
use Hennest\Money\Providers\MoneyServiceProvider;
use Hennest\Wallet\Providers\WalletServiceProvider;
use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    final protected function getPackageProviders($app): array
    {
        return [
            MathServiceProvider::class,
            MoneyServiceProvider::class,
            TestServiceProvider::class,
            WalletServiceProvider::class,
        ];
    }

    final protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], function (Repository $config): void {
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
        });
    }
}
