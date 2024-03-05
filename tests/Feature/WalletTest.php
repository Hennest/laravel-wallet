<?php

declare(strict_types=1);

use Hennest\Money\Money;
use Hennest\Wallet\Tests\Database\Factories\UserFactory;
use Hennest\Wallet\Tests\Database\Factories\WalletFactory;

test('model can create wallet', function (): void {
    $user = UserFactory::new()
        ->create();

    $user->createWallet([
        'name' => 'My Wallet',
        'slug' => 'my-wallet',
        'description' => 'My Wallet Description',
        'meta' => ['key' => 'value'],
        'decimal_places' => 2,
    ]);

    expect($user->wallet)
        ->name
        ->toEqual('My Wallet')
        ->slug
        ->toEqual('my-wallet')
        ->description
        ->toEqual('My Wallet Description')
        ->meta
        ->toEqual(['key' => 'value'])
        ->decimal_places
        ->toEqual(2);
});

test('model can have multiple wallets', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::zero()]))
        ->create();

    $user->createWallet([
        'name' => 'My Wallet',
        'slug' => 'my-wallet',
        'description' => 'My Wallet Description',
        'meta' => ['key' => 'value'],
        'decimal_places' => 2,
    ]);

    $this->assertDatabaseCount('wallets', 2);
    $this->assertDatabaseHas('wallets', [
        'name' => 'My Wallet',
        'slug' => 'my-wallet',
        'description' => 'My Wallet Description',
        'meta' => json_encode(['key' => 'value']),
        'decimal_places' => 2,
    ]);

    expect($user->getWallet('my-wallet'))
        ->name
        ->toEqual('My Wallet')
        ->slug
        ->toEqual('my-wallet')
        ->description
        ->toEqual('My Wallet Description')
        ->meta
        ->toEqual(['key' => 'value'])
        ->decimal_places
        ->toEqual(2);
});
