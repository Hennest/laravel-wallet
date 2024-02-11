<?php

declare(strict_types=1);

namespace Hennest\Wallet\Events;

final readonly class WalletCreatedEvent
{
    public function __construct(
        private int|string $id,
        private int|string $ownerId,
        private string $ownerType,
    ) {
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getOwnerId(): int|string
    {
        return $this->ownerId;
    }

    public function getOwnerType(): string
    {
        return $this->ownerType;
    }
}
