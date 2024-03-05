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
use Hennest\Wallet\Services\CastService;
use Hennest\Wallet\Services\ConsistencyService;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasWallet
{
    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function deposit(Money $amount, array|null $meta = [], bool $confirmed = true): Transaction
    {
        return app(DepositService::class)->handleOne(
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

        return $this->forceWithdraw(
            amount: $amount,
            meta: $meta,
            confirmed: $confirmed
        );
    }

    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function forceWithdraw(Money $amount, array|null $meta = [], bool $confirmed = true): Transaction
    {
        return app(WithdrawService::class)->handle(
            wallet: $this,
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta
        );
    }

    public function balance(): Attribute
    {
        return new Attribute(
            get: fn (): Money => app(CastService::class)->getWallet($this)->refresh()->balance
        );
    }
}
