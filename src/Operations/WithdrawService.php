<?php

declare(strict_types=1);

namespace Hennest\Wallet\Operations;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Money\Money;
use Hennest\Wallet\Assemblers\TransactionAssembler;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\Confirmable;
use Hennest\Wallet\Enums\TransactionStatus;
use Hennest\Wallet\Enums\TransactionType;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Models\Wallet;
use Hennest\Wallet\Services\CastService;
use Hennest\Wallet\Services\ConsistencyService;
use Hennest\Wallet\Services\TransactionService;
use Hennest\Wallet\Services\WalletService;

final readonly class WithdrawService
{
    public function __construct(
        private CastService $castService,
        private ConsistencyService $consistencyService,
        private TransactionAssembler $transactionAssembler,
        private TransactionService $transactionService,
        private WalletService $walletService,
    ) {
    }

    /**
     * @throws MathException
     * @throws RoundingNecessaryException
     * @throws AmountInvalid
     */
    public function handleOne(
        WalletInterface $owner,
        Money $amount,
        Confirmable $status,
        array|null $meta = [],
    ): Transaction {
        $this->consistencyService->ensurePositive(
            amount: $amount
        );

        $transaction = $this->transactionService->makeOne(
            owner: $owner,
            amount: $amount->negate(),
            type: TransactionType::Deposit,
            status: $status,
            meta: $meta
        );

        $status->isConfirmed() && $this->walletService->decrement(
            wallet: $this->castService->getWallet($owner),
            amount: $amount
        );

        return $transaction;
    }

    /**
     * @param array<int, Wallet> $wallets
     * @param array<int, Money> $amounts
     * @return Transaction[]
     * @throws MathException
     * @throws RoundingNecessaryException
     */
    public function handleMany(array $wallets, array $amounts): array
    {
        $this->consistencyService->ensureIntegrity(
            wallets: $wallets,
            amounts: $amounts
        );

        $transactionDtos = array_map(
            function (Wallet $wallet, Money $amount): TransactionDto {
                return $this->transactionAssembler->create(
                    walletId: $this->castService->getWallet($wallet)->getKey(),
                    owner: $this->castService->getWallet($wallet),
                    amount: $amount->negate(),
                    type: TransactionType::Withdraw,
                    status: TransactionStatus::Confirmed,
                    meta: []
                );
            },
            $wallets,
            $amounts
        );

        $transactions = $this->transactionService->createMany(
            transactionDtos: $transactionDtos
        );

        $this->walletService->decrementMany(
            wallets: $wallets,
            amounts: $amounts
        );

        return $transactions;
    }
}
