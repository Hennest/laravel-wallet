<?php

declare(strict_types=1);

namespace Hennest\Wallet\DTOs;

use Hennest\Money\Money;
use Hennest\Wallet\Enums\TransactionType;
use Illuminate\Contracts\Support\Arrayable;

final readonly class TransactionDto implements Arrayable
{
    public function __construct(
        private int|string $key,
        private TransactionType $type,
        private int|string $walletId,
        private int|string $payableId,
        private string $payableType,
        private Money $amount,
        private bool $confirmed,
        private array|null $meta
    ) {
    }

    public function getKey(): int|string
    {
        return $this->key;
    }

    public function getPayableType(): string
    {
        return $this->payableType;
    }

    public function getPayableId(): int|string
    {
        return $this->payableId;
    }

    public function getWalletId(): int|string
    {
        return $this->walletId;
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
            'id' => $this->key,
            'type' => $this->type,
            'wallet_id' => $this->walletId,
            'payable_id' => $this->payableId,
            'payable_type' => $this->payableType,
            'amount' => $this->amount,
            'confirmed' => $this->confirmed,
            'meta' => $this->meta,
        ];
    }
}
