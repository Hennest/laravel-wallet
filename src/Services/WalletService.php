<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Math\Contracts\MathServiceInterface;
use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Models\Wallet;
use Hennest\Wallet\Repository\WalletRepository;

final readonly class WalletService
{
    public function __construct(
        private MathServiceInterface $mathService,
        private WalletRepository $walletRepository,
    ) {
    }

    /**
     * @throws MathException
     * @throws RoundingNecessaryException
     */
    public function updateBalance(Wallet $wallet, TransactionDto $transactionDto): Wallet
    {
        $adjustedBalance = $this->mathService->add(
            first: $wallet->balance->format()->asMinorUnit(),
            second: $transactionDto->getAmount()->format()->asMinorUnit(),
        );

        return $this->walletRepository->updateBalance(
            wallet: $wallet,
            balance: new Money((int) $adjustedBalance)
        );
    }
}
