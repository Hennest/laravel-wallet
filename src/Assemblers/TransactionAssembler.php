<?php

declare(strict_types=1);

namespace Hennest\Wallet\Assemblers;

use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\TransactionType;
use Hennest\Wallet\Interfaces\WalletInterface;

final class TransactionAssembler
{
    public function create(
        WalletInterface $owner,
        Money $amount,
        TransactionType $type,
        bool $confirmed = true,
        array|null $meta = []
    ): TransactionDto {
        return new TransactionDto(
            walletId: $owner->wallet->getKey(),
            owner: $owner,
            type: $type,
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta,
        );
    }
}
