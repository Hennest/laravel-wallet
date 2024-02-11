<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Math\Contracts\MathServiceInterface;
use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Events\WalletCreatedEvent;
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
     * @param array{
     *     name: string,
     *     slug?: string,
     *     description?: string,
     *     meta?: array<array-key, mixed>|null,
     *     decimal_places?: positive-int,
     * } $attributes
     */
    public function createWallet(array $attributes): Wallet
    {
        $wallet = $this->walletRepository->create($attributes);

        event(new WalletCreatedEvent(
            id: $wallet->getKey(),
            ownerId: $wallet->owner_id,
            ownerType: $wallet->owner_type
        ));

        return $wallet;
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

        $wallet = $this->walletRepository->updateBalance(
            wallet: $wallet,
            balance: new Money((int) $adjustedBalance)
        );

        event(new WalletCreatedEvent(
            id: $wallet->getKey(),
            ownerId: $wallet->owner_id,
            ownerType: $wallet->owner_type
        ));

        return $wallet;
    }
}
