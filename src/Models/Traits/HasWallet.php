<?php

declare(strict_types=1);

namespace Hennest\Wallet\Models\Traits;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Money\Money;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Exceptions\BalanceIsEmpty;
use Hennest\Wallet\Exceptions\InsufficientFund;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Operations\DepositService;
use Hennest\Wallet\Operations\WithdrawService;
use Hennest\Wallet\Services\ConsistencyService;

trait HasWallet
{
    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function deposit(Money $amount, array|null $meta = [], bool $confirmed = true): Transaction
    {
        return app(DepositService::class)->handle(
            wallet: $this,
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta
        );
    }

    /**
     * @throws AmountInvalid
     * @throws MathException
     * @throws RoundingNecessaryException
     * @throws BalanceIsEmpty
     * @throws InsufficientFund
     */
    public function withdraw(Money $amount, array|null $meta = [], bool $confirmed = true): Transaction
    {
        app(ConsistencyService::class)->checkPotential($this->wallet, $amount);

        return app(WithdrawService::class)->handle(
            wallet: $this,
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta
        );
    }
}
