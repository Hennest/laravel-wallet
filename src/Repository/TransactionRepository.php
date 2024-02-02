<?php

declare(strict_types=1);

namespace Modules\Wallet\Repository;

use Modules\Wallet\DTOs\TransactionDto;
use Modules\Wallet\Models\Transaction;

final readonly class TransactionRepository
{
    public function __construct(
        private Transaction $transaction,
    ) {
    }

    /**
     * @param array<int|string, TransactionDto> $objects
     */
    public function insert(array $objects): bool
    {
        $transactions = [];

        foreach ($objects as $object) {
            $transactions[] = $object->toArray();
        }

        return $this->transaction->insert($transactions);
    }

    public function insertOne(TransactionDto $transactionDto): Transaction
    {
        $instance = $this
            ->transaction
            ->newInstance($transactionDto->toArray());
        $instance->saveQuietly();

        return $instance;
    }
}