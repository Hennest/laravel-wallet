<?php

declare(strict_types=1);

namespace Hennest\Wallet\Operations;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
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
        private TransactionService $transactionService,
        private WalletService $walletService,
    ) {
    }

    /**
     * @throws MathException
     * @throws RoundingNecessaryException
     * @throws AmountInvalid
     */
    public function handle(
        WalletInterface $wallet,
        Money $amount,
        bool $confirmed = true,
        array|null $meta = [],
    ): Transaction {
        $this->consistencyService->ensurePositive(
            amount: $amount
        );

        $wallet = $this->castService->getWallet($wallet);

        $transactionDto = new TransactionDto(
            walletId: $wallet->getKey(),
            owner: $this->castService->getOwner($wallet),
            type: TransactionType::Withdraw,
            amount: $amount->negate(),
            confirmed: $confirmed,
            meta: $meta,
        );

        $transaction = $this->transactionService->create(
            transactionDto: $transactionDto
        );


        if ($transactionDto->getConfirmed()) {
            $this->walletService->decrement(
                wallet: $wallet,
                amount: $transactionDto->getAmount()
            );
        }

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
            fn (Wallet $wallet, Money $amount): TransactionDto => new TransactionDto(
                walletId: $this->castService->getWallet($wallet)->getKey(),
                owner: $this->castService->getOwner($wallet),
                type: TransactionType::Withdraw,
                amount: $amount->negate(),
                confirmed: true,
                meta: [],
            ),
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
