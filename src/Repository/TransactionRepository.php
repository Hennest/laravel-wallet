<?php

declare(strict_types=1);

namespace Hennest\Wallet\Repository;

use Hennest\Wallet\DTOs\TransactionDto;
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
}
