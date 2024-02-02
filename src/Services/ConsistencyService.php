<?php

declare(strict_types=1);

namespace Modules\Wallet\Services;

use Brick\Math\Exception\MathException;
use Modules\Math\Contracts\MathServiceInterface;
use Modules\Money\Money;
use Modules\Wallet\Exceptions\AmountInvalid;
use Modules\Wallet\Exceptions\BalanceIsEmpty;
use Modules\Wallet\Exceptions\InsufficientFund;
use Modules\Wallet\Models\Wallet;

final class ConsistencyService
{
    public function __construct(
        protected MathServiceInterface $mathService
    ) {
    }

    /**
     * @throws AmountInvalid
     * @throws MathException
     */
    public function checkPositive(Money $amount): void
    {
        if (MathServiceInterface::LESS_THAN_FIRST_NUMBER === $this->compare($amount, Money::zero())) {
            throw new AmountInvalid(
                message: 'Amount must be positive'
            );
        }
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFund
     * @throws MathException
     */
    public function checkPotential(Wallet $wallet, Money $amount, bool $allowZero = false): void
    {
        $isZero = fn (Money $amount) => MathServiceInterface::EQUAL_TO_FIRST_NUMBER === $this->compare($amount, Money::zero());

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

        return $this->compare($balance, $amount) >= MathServiceInterface::EQUAL_TO_FIRST_NUMBER;
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
