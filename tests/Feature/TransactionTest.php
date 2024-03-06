<?php

declare(strict_types=1);

use Hennest\Money\Money;
use Hennest\Wallet\Enums\TransactionStatus;
use Hennest\Wallet\Enums\TransactionType;
use Hennest\Wallet\Services\TransactionService;
use Hennest\Wallet\Tests\Database\Factories\UserFactory;
use Hennest\Wallet\Tests\Database\Factories\WalletFactory;

test('Transaction can be confirmed', function (): void {
    $user = UserFactory::new()
        ->has(WalletFactory::new(['balance' => Money::of(30)]))
        ->create();

    $transaction = app(TransactionService::class)->makeOne(
        owner: $user,
        amount: Money::of(20),
        type: TransactionType::Deposit,
        status: TransactionStatus::Pending,
    );

    $this->assertDatabaseCount('transactions', 1);

    expect($transaction->status->isConfirmed())->toBeFalse();

    $transaction->confirm();

    expect($transaction->status->isConfirmed())->toBeTrue();
});
