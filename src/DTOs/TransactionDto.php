<?php

declare(strict_types=1);

namespace Hennest\Wallet\DTOs;

use Hennest\Money\Money;
use Hennest\Wallet\Enums\TransactionType;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

final readonly class TransactionDto implements Arrayable
{
    public function __construct(
        private int|string $walletId,
        private Model $owner,
        private TransactionType $type,
        private Money $amount,
        private bool $confirmed,
        private array|null $meta
    ) {
    }

    public function getWalletId(): int|string
    {
        return $this->walletId;
    }

    public function getOwner(): Model
    {
        return $this->owner;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function getMeta(): array|null
    {
        return $this->meta;
    }

    public function toArray(): array
    {
        return [
            'wallet_id' => $this->walletId,
            'payable_id' => $this->owner->getKey(),
            'payable_type' => $this->owner->getMorphClass(),
            'type' => $this->type,
            'amount' => $this->amount,
            'confirmed' => $this->confirmed,
            'meta' => $this->meta,
        ];
    }
}
