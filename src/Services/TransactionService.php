<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Brick\Math\Exception\MathException;
use Hennest\Money\Money;
use Hennest\Wallet\Assemblers\TransactionAssembler;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\TransactionType;
use Hennest\Wallet\Events\TransactionCreatedEvent;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Repository\TransactionRepository;

final readonly class TransactionService
{
    public function __construct(
        private ConsistencyService $consistencyService,
        private TransactionRepository $transactionRepository,
        private TransactionAssembler $transactionAssembler,
    ) {
    }

    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function makeOne(
        WalletInterface $owner,
        Money $amount,
        TransactionType $type,
        bool $confirmed = true,
        array|null $meta = []
    ): Transaction {
        $this->consistencyService->ensurePositive(
            amount: $amount
        );

        $transactionDto = $this->transactionAssembler->create(
            owner: $owner,
            amount: $amount,
            type: $type,
            confirmed: $confirmed,
            meta: $meta
        );

        return $this->create(
            transactionDto: $transactionDto
        );
    }

    public function create(TransactionDto $transactionDto): Transaction
    {
        $transaction = $this->transactionRepository->create($transactionDto);

        event(new TransactionCreatedEvent(
            id: $transaction->getKey(),
            type: $transaction->type,
            walletId: $transaction->wallet_id,
            confirmed: $transaction->confirmed
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
