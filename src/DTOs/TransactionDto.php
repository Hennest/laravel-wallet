<?php

declare(strict_types=1);

namespace Modules\Wallet\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Modules\Wallet\Enums\TransactionType;

final readonly class TransactionDto implements Arrayable
{
    public function __construct(
        private int|string $key,
        private TransactionType $type,
        private int|string $walletId,
        private int|string $payableId,
        private string $payableType,
        private int $amount,
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

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getAmount(): int
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
            '',
        ];
    }
}
