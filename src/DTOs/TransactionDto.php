<?php

declare(strict_types=1);

namespace Hennest\Wallet\DTOs;

use Hennest\Money\Money;
use Hennest\Wallet\Enums\TransactionType;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class TransactionDto implements Arrayable
{
    private string|int|null $id;

    public function __construct(
        private readonly int|string $walletId,
        private readonly Model $owner,
        private readonly TransactionType $type,
        private readonly Money $amount,
        private readonly bool $confirmed,
        private readonly array|null $meta,
    ) {
        $this->id = (string) Str::ulid();
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

    public function getConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function getMeta(): array|null
    {
        return $this->meta;
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->walletId,
            'payable_id' => $this->owner->getKey(),
            'payable_type' => $this->owner->getMorphClass(),
            'type' => $this->type,
            'amount' => $this->amount,
            'confirmed' => $this->confirmed,
            // TODO: 'meta' => $this->meta,
        ];
    }

    public function all(): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->walletId,
            'payable_id' => $this->owner->getKey(),
            'payable_type' => $this->owner->getMorphClass(),
            'type' => $this->type->value,
            'amount' => $this->amount->format()->asMinorUnit(),
            'confirmed' => $this->confirmed,
            // TODO: 'meta' => $this->meta,
        ];
    }
}
