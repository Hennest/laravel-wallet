<?php

declare(strict_types=1);

namespace Hennest\Wallet\Events;

use Hennest\Wallet\Enums\Confirmable;
use Hennest\Wallet\Enums\TransactionType;

final readonly class TransactionConfirmedEvent
{
    public function __construct(
        private int|string $id,
        private TransactionType $type,
        private int|string $walletId,
        private Confirmable $status,
    ) {
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getWalletId(): int|string
    {
        return $this->walletId;
    }

    public function getStatus(): Confirmable
    {
        return $this->status;
    }
}
