<?php

declare(strict_types=1);

use Hennest\Money\Money;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Exceptions\BalanceIsEmpty;
use Hennest\Wallet\Exceptions\InsufficientFund;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Tests\Database\Factories\UserFactory;
use Hennest\Wallet\Tests\Database\Factories\WalletFactory;

test('wallet can deposit', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::zero()]))
        ->create();

    $user->deposit(Money::of(10));
    expect($user->wallet->balance)->toEqual(Money::of(10));

    $user->deposit(Money::of(10));
    expect($user->wallet->balance)->toEqual(Money::of(20));

    $user->deposit(Money::of(30));
    expect($user->wallet->balance)->toEqual(Money::of(50));

    $this->assertDatabaseCount('transactions', 3);
    $this->assertDatabaseHas('wallets', [
        'balance' => 50,
    ]);

    expect(Transaction::query()->sum('amount'))->toEqual(50);
});

test('wallet can withdraw ', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::of(30)]))
        ->create();

    $user->withdraw(Money::of(10));
    expect($user->wallet->balance)->toEqual(Money::of(20));

    $user->withdraw(Money::of(10));
    expect($user->wallet->balance)->toEqual(Money::of(10));

    $user->withdraw(Money::of(10));
    expect($user->wallet->balance)->toEqual(Money::of(0));

    $this->assertDatabaseCount('transactions', 3);
    $this->assertDatabaseHas('wallets', [
        'balance' => 0,
    ]);

    expect(Transaction::query()->sum('amount'))->toEqual(-30);
});

test('wallet can not deposit with invalid amount', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::zero()]))
        ->create();

    expect(
        fn (): Transaction => $user->deposit(Money::of(-10))
    )->toThrow(AmountInvalid::class);

    $this->assertDatabaseCount('transactions', 0);
});

test('wallet can not withdraw with invalid amount', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::of(10)]))
        ->create();

    expect(
        fn (): Transaction => $user->withdraw(Money::of(-10))
    )->toThrow(AmountInvalid::class);

    $this->assertDatabaseCount('transactions', 0);
});

test('wallet can not withdraw with empty Balance', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::zero()]))
        ->create();

    expect(
        fn (): Transaction => $user->withdraw(Money::of(-10))
    )->toThrow(BalanceIsEmpty::class);

    $this->assertDatabaseCount('transactions', 0);
});

test('wallet can not withdraw with insufficient balance', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::of(10)]))
        ->create();

    expect(
        fn (): Transaction => $user->withdraw(Money::of(20))
    )->toThrow(InsufficientFund::class);

    $this->assertDatabaseCount('transactions', 0);
});
