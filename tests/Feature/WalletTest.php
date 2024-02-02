<?php

declare(strict_types=1);

use Hennest\Money\Money;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Tests\Database\Factories\UserFactory;
use Hennest\Wallet\Tests\Database\Factories\WalletFactory;
use Hennest\Wallet\Wallet;

test('wallet can deposit ', function (): void {
    $walletModel = WalletFactory::new()
        ->for(UserFactory::new(), 'holder')
        ->create([
            'balance' => Money::zero(),
        ]);

    (new Wallet($walletModel))->deposit(new Money(200));

    $transaction = Transaction::query()->first();

    $this->assertDatabaseCount('transactions', 1);

    expect($transaction->amount)->toEqual(new Money(200));
});
