<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Events\TransactionCreatedEvent;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Repository\TransactionRepository;

final readonly class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function create(TransactionDto $transactionDto): Transaction
    {
        $transaction = $this->transactionRepository->create($transactionDto);

        event(new TransactionCreatedEvent(
            id: $transaction->getKey(),
            type: $transaction->type,
            walletId: $transaction->wallet_id,
        ));

        return $transaction;
    }

    /**
     * @param array<int|string, TransactionDto> $transactionDtos
     * @return array<int|string, Transaction>
     */
    public function createMany(array $transactionDtos): array
    {
        if (1 === count($transactionDtos)) {
            $transactions = [$this->transactionRepository->create(reset($transactionDtos))];
        } else {
            $transactionIds = $this->transactionRepository->insert($transactionDtos);
            $transactions = $this->transactionRepository->findById($transactionIds);
        }

        /** @var Transaction[] $transactions */
        foreach ($transactions as $transaction) {
            event(new TransactionCreatedEvent(
                id: $transaction->id,
                type: $transaction->type,
                walletId: $transaction->wallet_id,
            ));
        }

        return $transactions;
    }
}
