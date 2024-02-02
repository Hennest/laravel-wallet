<?php

declare(strict_types=1);

namespace Modules\Wallet\Services;

use Brick\Math\Exception\MathException;
use Modules\Money\Money;
use Modules\Wallet\DTOs\TransactionDto;
use Modules\Wallet\Enums\TransactionType;
use Modules\Wallet\Events\TransactionCreatedEvent;
use Modules\Wallet\Exceptions\AmountInvalid;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Repository\TransactionRepository;

final readonly class TransactionService
{
    public function __construct(
        private ConsistencyService $consistencyService,
        private TransactionRepository $transactionRepository,
    ) {
    }

    /**
     * @throws MathException
     * @throws AmountInvalid
     */
    public function create(
        Wallet $wallet,
        TransactionType $type,
        Money $amount,
        bool $confirmed = true,
        array|null $meta = [],
    ): TransactionDto {
        $this->consistencyService->checkPositive($amount);

        $transaction = new TransactionDto(
            key: $wallet->getKey(),
            type: $type,
            walletId: $wallet->getKey(),
            payableId: $wallet->owner->getKey(),
            payableType: $wallet->owner->getMorphClass(),
            amount: $amount->format()->asMinorUnit(),
            confirmed: $confirmed,
            meta: $meta,
        );

        $this->apply([
            $transaction,
        ]);

        return $transaction;
    }

    /**
     * @param array<int|string, TransactionDto> $objects
     * @return array<int|string, TransactionDto>
     */
    public function apply(array $objects): array
    {
        if (1 === count($objects)) {
            $this->transactionRepository->insertOne(reset($objects));
        } else {
            $this->transactionRepository->insert($objects);
        }

        foreach ($objects as $object) {
            event(new TransactionCreatedEvent(
                id: $object->getKey(),
                type: $object->getType(),
                walletId: $object->getWalletId(),
            ));
        }

        return $objects;
    }
}
