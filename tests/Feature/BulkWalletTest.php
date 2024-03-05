<?php

declare(strict_types=1);

use Hennest\Money\Money;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Operations\DepositService;
use Hennest\Wallet\Tests\Database\Factories\UserFactory;
use Hennest\Wallet\Tests\Database\Factories\WalletFactory;

test('wallet can deposit in bulk', function (): void {
    [$user1, $user2] = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::of(2)]))
        ->count(2)
        ->create();

    app(DepositService::class)->handleMany(
        wallets: [$user1->wallet, $user2->wallet],
        amounts: [Money::of(10), Money::of(10)]
    );

    $this->assertDatabaseCount('wallets', 2);
    $this->assertDatabaseCount('transactions', 2);
    $this->assertDatabaseHas('wallets', [
        'balance' => 10,
    ]);

    expect(Transaction::query()->sum('amount'))
        ->toEqual(20)
        ->and($user1->balance)
        ->toEqual(Money::of(10))
        ->and($user2->balance)
        ->toEqual(Money::of(10));
});

test('wallet can not deposit with less number of amount or wallet', function (): void {
    $user1 = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::of(2)]))
        ->create();

    expect(fn (): array => app(DepositService::class)->handleMany(
        wallets: [$user1->wallet],
        amounts: [Money::of(10), Money::of(10)]
    ))->toThrow(InvalidArgumentException::class);
});
