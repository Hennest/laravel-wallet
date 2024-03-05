<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;
use Hennest\Math\Contracts\MathServiceInterface;
use Hennest\Money\Money;
use Hennest\Wallet\Events\BalanceUpdatedEvent;
use Hennest\Wallet\Events\WalletCreatedEvent;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Wallet;
use Hennest\Wallet\Repository\WalletRepository;

final readonly class WalletService
{
    public function __construct(
        private MathServiceInterface $mathService,
        private WalletRepository $walletRepository,
        private ConsistencyService $consistencyService,
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
    public function createWallet(WalletInterface $model, array $attributes): Wallet
    {
        $wallet = $this->walletRepository->create([
            ...[
                'owner_id' => $model->getKey(),
                'owner_type' => $model->getMorphClass(),
            ],
            ...$attributes
        ]);

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
    public function increment(Wallet $wallet, Money $amount): Wallet
    {
        $adjustedBalance = $this->mathService->add(
            first: $wallet->balance->format()->asMinorUnit(),
            second: $amount->format()->asMinorUnit(),
        );

        return $this->updateBalance(
            wallet: $wallet,
            amount: new Money((int) $adjustedBalance)
        );
    }

    /**
     * @throws MathException
     * @throws RoundingNecessaryException
     */
    public function decrement(Wallet $wallet, Money $amount): Wallet
    {
        $adjustedBalance = $this->mathService->subtract(
            first: $wallet->balance->format()->asMinorUnit(),
            second: $amount->absolute()->format()->asMinorUnit(),
        );

        return $this->updateBalance(
            wallet: $wallet,
            amount: new Money((int) $adjustedBalance)
        );
    }

    /**
     * @param array<int, Wallet> $wallets
     * @param array<int, Money> $amounts
     * @return Wallet[]
     * @throws MathException
     * @throws RoundingNecessaryException
     */
    public function incrementMany(array $wallets, array $amounts): array
    {
        $this->consistencyService->ensureConsistency(
            wallets: $wallets,
            amounts: $amounts
        );

        $adjustedBalances = array_map(
            fn (Wallet $wallet, Money $amount): Money => new Money(
                minorUnit: (int) $this->mathService->add(
                    first: $wallet->balance->format()->asMinorUnit(),
                    second: $amount->format()->asMinorUnit(),
                )
            ),
            $wallets,
            $amounts
        );

        return $this->updateBalances(
            wallets: $wallets,
            amounts: $adjustedBalances
        );
    }

    /**
     * @param array<int, Wallet> $wallets
     * @param array<int, Money> $amounts
     * @return Wallet[]
     * @throws MathException
     * @throws RoundingNecessaryException
     */
    public function decrementMany(array $wallets, array $amounts): array
    {
        $this->consistencyService->ensureConsistency(
            wallets: $wallets,
            amounts: $amounts
        );

        $adjustedBalances = array_map(
            fn (Wallet $wallet, Money $amount): Money => new Money(
                minorUnit: (int) $this->mathService->subtract(
                    first: $wallet->balance->format()->asMinorUnit(),
                    second: $amount->format()->asMinorUnit(),
                )
            ),
            $wallets,
            $amounts
        );

        return $this->updateBalances(
            wallets: $wallets,
            amounts: $adjustedBalances
        );
    }

    public function updateBalance(Wallet $wallet, Money $amount): Wallet
    {
        $wallet = $this->walletRepository->updateBalance(
            wallet: $wallet,
            amount:  $amount
        );

        event(new BalanceUpdatedEvent(
            walletId: $wallet->getKey(),
            balance: $wallet->balance
        ));

        return $wallet;
    }

    /**
     * @param array<int, Wallet> $wallets
     * @param array<int, Money> $amounts
     * @return Wallet[]
     */
    public function updateBalances(array $wallets, array $amounts): array
    {
        $this->consistencyService->ensureConsistency(
            wallets: $wallets,
            amounts: $amounts
        );

        $walletIds = $this->walletRepository->updateBalances(
            wallets: $wallets,
            amounts: $amounts
        );
        $wallets = $this->walletRepository->findByIds(
            walletIds: $walletIds
        );

        foreach ($wallets as $wallet) {
            event(new BalanceUpdatedEvent(
                walletId: $wallet->getKey(),
                balance: $wallet->balance
            ));
        }

        return $wallets;
    }
}
