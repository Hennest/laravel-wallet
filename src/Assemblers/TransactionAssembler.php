<?php

declare(strict_types=1);

namespace Hennest\Wallet\Assemblers;

use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\Confirmable;
use Hennest\Wallet\Enums\HasType;
use Hennest\Wallet\Interfaces\WalletInterface;

final class TransactionAssembler
{
    public function create(
        int|string $walletId,
        WalletInterface $owner,
        Money $amount,
        HasType $type,
        Confirmable $status,
        array|null $meta = []
    ): TransactionDto {
        return new TransactionDto(
            walletId: $walletId,
            owner: $owner,
            type: $type,
            amount: $amount,
            status: $status,
            meta: $meta,
        );
    }
}
