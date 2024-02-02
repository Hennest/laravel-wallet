<?php

declare(strict_types=1);

namespace Modules\Wallet\Events;

use Modules\Wallet\Enums\TransactionType;

final readonly class TransactionCreatedEvent
{
    public function __construct(
        private int $id,
        private TransactionType $type,
        private int $walletId,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }
}
