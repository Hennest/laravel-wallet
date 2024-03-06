<?php

declare(strict_types=1);

namespace Hennest\Wallet\Repository;

use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\TransactionStatus;
use Hennest\Wallet\Models\Transaction;

final readonly class TransactionRepository
{
    public function __construct(
        private Transaction $transaction,
    ) {
    }

    public function create(TransactionDto $transactionDto): Transaction
    {
        $instance = $this
            ->transaction
            ->newInstance($transactionDto->toArray());
        $instance->saveQuietly();

        return $instance;
    }

    /**
     * @param array<int|string, TransactionDto> $transactionDtos
     * @return array<int, int|string>
     */
    public function insert(array $transactionDtos): array
    {
        $dtosToArray = [];

        foreach ($transactionDtos as $transactionDto) {
            $dtosToArray[] = $transactionDto->all();
        }

        $this->transaction->newQuery()->insert($dtosToArray);

        return array_map(
            fn (TransactionDto $transactionDto): int|string => $transactionDto->getId(),
            $transactionDtos
        );
    }

    public function confirm(Transaction $transaction): Transaction
    {
        $transaction->fill([
            'status' => TransactionStatus::Confirmed
        ]);

        $transaction->saveQuietly();

        return $transaction;
    }

    /**
     * @param array<string, int|string> $transactionIds
     * @return array<int, Transaction>
     */
    public function findById(array $transactionIds): array
    {
        return $this->transaction->newQuery()
            ->whereIn('id', $transactionIds)
            ->get()
            ->all();
    }
}
