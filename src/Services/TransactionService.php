<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Hennest\Money\Money;
use Hennest\Wallet\Assemblers\TransactionAssembler;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\Confirmable;
use Hennest\Wallet\Enums\HasType;
use Hennest\Wallet\Events\TransactionConfirmedEvent;
use Hennest\Wallet\Events\TransactionCreatedEvent;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Repository\TransactionRepository;

final readonly class TransactionService
{
    public function __construct(
        private CastService $castService,
        private TransactionRepository $transactionRepository,
        private TransactionAssembler $transactionAssembler,
    ) {
    }

    public function makeOne(
        WalletInterface $owner,
        Money $amount,
        HasType $type,
        Confirmable $status,
        array|null $meta = []
    ): Transaction {
        $transactionDto = $this->transactionAssembler->create(
            walletId: $this->castService->getWallet($owner)->getKey(),
            owner: $this->castService->getOwner($owner),
            amount: $amount,
            type: $type,
            status: $status,
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
            status: $transaction->status
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
                status: $transaction->status
            ));
        }

        return $transactions;
    }

    public function confirm(Transaction $transaction): Transaction
    {
        if ($transaction->status->isConfirmed()) {
            return $transaction;
        }

        $transaction = $this->transactionRepository->confirm($transaction);

        event(new TransactionConfirmedEvent(
            id: $transaction->getKey(),
            type: $transaction->type,
            walletId: $transaction->wallet_id,
            status: $transaction->status,
        ));

        return $transaction;
    }
}
