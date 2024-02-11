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
        $this->consistencyService->checkPositive(
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
            $this->walletService->updateBalance($wallet, $transactionDto);
        }

        return $transaction;
    }
}
