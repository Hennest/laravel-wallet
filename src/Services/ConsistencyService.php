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
    public function checkPositive(Money $amount): void
    {
        if (MathServiceInterface::FIRST_NUMBER_IS_LESSER === $this->compare($amount, Money::zero())) {
            throw new AmountInvalid(
                message: 'Amount must be positive'
            );
        }

        if (MathServiceInterface::THEY_ARE_EQUAL === $this->compare($amount, Money::zero())) {
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
    public function checkPotential(WalletInterface $wallet, Money $amount, bool $allowZero = false): void
    {
        $wallet = $this->castService->getWallet($wallet);
        $isZero = fn (
            Money $amount
        ): bool => MathServiceInterface::THEY_ARE_EQUAL === $this->compare($amount, Money::zero());

        if ( ! $isZero($amount) && $isZero($wallet->balance)) {
            throw new BalanceIsEmpty(
                message: 'Balance is empty'
            );
        }

        if ( ! $this->canWithdraw($wallet->balance, $amount, $allowZero)) {
            throw new InsufficientFund(
                message: 'Insufficient funds'
            );
        }
    }

    /**
     * @throws MathException
     */
    public function canWithdraw(Money $balance, Money $amount, bool $allowZero = false): bool
    {
        /**
         * Allow withdrawal with a negative balance.
         */
        if ($allowZero && ! $this->compare($amount, Money::zero())) {
            return true;
        }

        return $this->compare($balance, $amount) >= MathServiceInterface::THEY_ARE_EQUAL;
    }

    /**
     * @throws MathException
     */
    private function compare(Money $firstMoney, Money $secondMoney): int
    {
        return $this->mathService->compare(
            first: $firstMoney->format()->asMinorUnit(),
            second: $secondMoney->format()->asMinorUnit()
        );
    }
}
