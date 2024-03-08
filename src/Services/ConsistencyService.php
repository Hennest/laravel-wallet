<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Brick\Math\Exception\MathException;
use Hennest\Math\Contracts\MathServiceInterface;
use Hennest\Money\Money;
use Hennest\Wallet\Exceptions\AmountInvalid;
use Hennest\Wallet\Exceptions\BalanceIsEmpty;
use Hennest\Wallet\Exceptions\InsufficientFund;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Wallet;
use InvalidArgumentException;
use LengthException;

final class ConsistencyService
{
    public function __construct(
        protected CastService $castService,
        protected MathServiceInterface $mathService,
    ) {
    }

    /**
     * @throws AmountInvalid
     * @throws MathException
     */
    public function ensurePositive(Money $amount): void
    {
        if ($this->mathService->lessThan($amount->format()->asMinorUnit(), 0)) {
            throw new AmountInvalid(
                message: 'Amount must be positive'
            );
        }

        if ($this->mathService->equals($amount->format()->asMinorUnit(), 0)) {
            throw new AmountInvalid(
                message: 'Amount cannot be zero'
            );
        }
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFund
     * @throws MathException
     */
    public function ensureSufficientBalance(WalletInterface $wallet, Money $amount): void
    {
        $wallet = $this->castService->getWallet($wallet);
        $isZero = fn (Money $amount): bool => $this->mathService->equals(
            first: $amount->format()->asMinorUnit(),
            second: Money::zero()->format()->asMinorUnit()
        );

        if ( ! $isZero($amount) && $isZero($wallet->balance)) {
            throw new BalanceIsEmpty(
                message: 'Balance is empty'
            );
        }

        if ( ! $this->canWithdraw($wallet->balance, $amount)) {
            throw new InsufficientFund(
                message: 'Insufficient funds'
            );
        }
    }

    /**
     * @throws MathException
     */
    public function canWithdraw(Money $balance, Money $amount): bool
    {
        return $this->mathService->greaterThanOrEqual(
            first: $balance->format()->asMinorUnit(),
            second: $amount->format()->asMinorUnit()
        );
    }

    /**
     * @param array<array-key, Wallet> $wallets
     * @param array<array-key, Money> $amounts
     */
    public function ensureIntegrity(array $wallets, array $amounts): void
    {
        if (empty($wallets) && empty($amounts)) {
            throw new InvalidArgumentException('Wallets and amounts must not be empty');
        }

        if (count($wallets) !== count($amounts)) {
            throw new LengthException('Wallets and amounts must have the same length');
        }
    }
}
