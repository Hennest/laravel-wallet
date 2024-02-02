<?php

declare(strict_types=1);

namespace Hennest\Wallet;

use Brick\Math\Exception\MathException;
use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\TransactionType;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Services\TransactionService;

final readonly class Wallet
{
    public function __construct(
        public WalletInterface $wallet,
    ) {
    }

    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function deposit(Money $amount, bool $confirmed = true, array|null $meta = []): TransactionDto
    {
        return app(TransactionService::class)->create(
            wallet: $this->wallet,
            type: TransactionType::Deposit,
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta
        );
    }

    public function withdraw(): void
    {

    }

    public function forceWithdraw(): void
    {

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
