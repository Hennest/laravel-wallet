<?php

declare(strict_types=1);

namespace Hennest\Wallet\Operations;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Money\Money;
use Hennest\Wallet\Assemblers\TransactionAssembler;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Enums\TransactionType;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Transaction;
use Hennest\Wallet\Models\Wallet;
use Hennest\Wallet\Services\ConsistencyService;
use Hennest\Wallet\Services\TransactionService;
use Hennest\Wallet\Services\WalletService;

final readonly class DepositService
{
    public function __construct(
        private ConsistencyService $consistencyService,
        private TransactionService $transactionService,
        private WalletService $walletService,
        private TransactionAssembler $transactionAssembler,
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
        bool $confirmed,
        array|null $meta = [],
    ): Transaction {
        $transaction = $this->transactionService->makeOne(
            owner: $owner,
            amount: $amount,
            type: TransactionType::Deposit,
            confirmed: $confirmed,
            meta: $meta
        );

        $transaction->confirmed && $this->walletService->increment(
            wallet: $owner->wallet,
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
                    owner: $wallet,
                    amount: $amount,
                    type: TransactionType::Deposit,
                    confirmed: true,
                    meta: []
                );
            },
            $wallets,
            $amounts
        );

        $transactions = $this->transactionService->createMany(
            transactionDtos: $transactionDtos
        );

        $this->walletService->incrementMany(
            wallets: $wallets,
            amounts: $amounts
        );

        return $transactions;
    }
}
