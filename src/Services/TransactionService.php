<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Brick\Math\Exception\MathException;
use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\TransactionType;
use Hennest\Wallet\Events\TransactionCreatedEvent;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Repository\TransactionRepository;

final readonly class TransactionService
{
    public function __construct(
        private ConsistencyService $consistencyService,
        private TransactionRepository $transactionRepository,
    ) {
    }

    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function create(
        WalletInterface $wallet,
        TransactionType $type,
        Money $amount,
        bool $confirmed = true,
        array|null $meta = [],
    ): TransactionDto {
        $this->consistencyService->checkPositive($amount);

        $transaction = new TransactionDto(
            key: $wallet->getKey(),
            type: $type,
            walletId: $wallet->getKey(),
            payableId: $wallet->holder->getKey(),
            payableType: $wallet->holder->getMorphClass(),
            amount: $amount,
            confirmed: $confirmed,
            meta: $meta,
        );

        $this->apply([
            $transaction,
        ]);

        return $transaction;
    }

    /**
     * @param array<int|string, TransactionDto> $transactionDtos
     * @return array<int|string, TransactionDto>
     */
    public function apply(array $transactionDtos): array
    {
        if (1 === count($transactionDtos)) {
            $this->transactionRepository->insertOne(reset($transactionDtos));
        } else {
            $this->transactionRepository->insert($transactionDtos);
        }

        foreach ($transactionDtos as $object) {
            event(new TransactionCreatedEvent(
                id: $object->getKey(),
                type: $object->getType(),
                walletId: $object->getWalletId(),
            ));
        }

        return $transactionDtos;
    }
}
