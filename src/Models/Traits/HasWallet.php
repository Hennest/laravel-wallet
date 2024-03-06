<?php

declare(strict_types=1);

namespace Hennest\Wallet\Models\Traits;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Money\Money;
use Hennest\Wallet\Enums\Confirmable;
use Hennest\Wallet\Enums\TransactionStatus;
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
    public function deposit(Money $amount, array|null $meta = [], Confirmable $status = TransactionStatus::Confirmed): Transaction
    {
        return app(DepositService::class)->handleOne(
            owner: $this,
            amount: $amount,
            status: $status,
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
    public function withdraw(Money $amount, array|null $meta = [], Confirmable $status = TransactionStatus::Confirmed): Transaction
    {
        app(ConsistencyService::class)->ensureSufficientBalance($this->wallet, $amount);

        return $this->forceWithdraw(
            amount: $amount,
            meta: $meta,
            status: $status
        );
    }

    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function forceWithdraw(Money $amount, array|null $meta = [], Confirmable $status = TransactionStatus::Confirmed): Transaction
    {
        return app(WithdrawService::class)->handleOne(
            owner: $this,
            amount: $amount,
            status: $status,
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
