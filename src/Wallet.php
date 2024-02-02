<?php

declare(strict_types=1);

namespace Modules\Wallet;

use Brick\Math\Exception\MathException;
use Modules\Money\Money;
use Modules\Wallet\DTOs\TransactionDto;
use Modules\Wallet\Enums\TransactionType;
use Modules\Wallet\Exceptions\AmountInvalid;
use Modules\Wallet\Services\TransactionService;

final readonly class Wallet
{
    public function __construct(
        public Models\Wallet $wallet,
        public string $name,
        public string $type,
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
