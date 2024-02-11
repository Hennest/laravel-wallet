<?php

declare(strict_types=1);

namespace Hennest\Wallet;

use Brick\Math\Exception\MathException;
use Hennest\Money\Money;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Exceptions\BalanceIsEmpty;
use Hennest\Wallet\Exceptions\InsufficientFund;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Operations\DepositService;
use Hennest\Wallet\Operations\WithdrawService;
use Hennest\Wallet\Repository\WalletRepository;
use Hennest\Wallet\Services\ConsistencyService;

final readonly class Wallet
{
    public function __construct(
        public WalletInterface $wallet,
    ) {
    }

    /**
     * @param array{
     *     name: string,
     *     slug?: string,
     *     description?: string,
     *     meta?: array<array-key, mixed>|null,
     *     decimal_places?: positive-int,
     * } $attributes
     */
    public function create(array $attributes)
    {
        return app(WalletRepository::class)->create($attributes);
    }

    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function deposit(Money $amount, array|null $meta = [], bool $confirmed = true): Transaction
    {
        return app(DepositService::class)->handleOne(
            wallet: $this->wallet,
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta
        );
    }

    /**
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFund
     * @throws MathException
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
            wallet: $this->wallet,
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta
        );
    }

    public function transfer(): void
    {

    }

    public function safeTransfer(): void
    {

    }

    public function forceTransfer(): void
    {

    }

    public function canWithdraw(): void
    {

    }

    public function balance(): void
    {

    }
}
