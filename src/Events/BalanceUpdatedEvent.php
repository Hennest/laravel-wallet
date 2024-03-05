<?php

declare(strict_types=1);

namespace Hennest\Wallet\Events;

use Hennest\Money\Money;

final readonly class BalanceUpdatedEvent
{
    public function __construct(
        private int|string $walletId,
        private Money $balance,
    ) {
    }

    public function getWalletId(): int|string
    {
        return $this->walletId;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }
}
