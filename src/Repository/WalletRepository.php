<?php

declare(strict_types=1);

namespace Hennest\Wallet\Repository;

use Hennest\Money\Money;
use Hennest\Wallet\DTOs\TransactionDto;
use Hennest\Wallet\Models\Wallet;

final readonly class WalletRepository
{
    public function __construct(
        private Wallet $wallet
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
    public function create(array $attributes): Wallet
    {
        $wallet = $this->wallet->newInstance($attributes);

        $wallet->saveQuietly();

        return $wallet;
    }

    public function updateBalance(Wallet $wallet, Money $balance): Wallet
    {
        $wallet->fill([
            'balance' => $balance
        ]);

        $wallet->saveQuietly();

        return $wallet;
    }

    /**
     * @param array<string, TransactionDto> $transactionDtos
     * @return array<int, int|string>
     */
    public function updateBalances(array $transactionDtos): array
    {
        $cases = '';
        $walletIds = [];
        foreach ($transactionDtos as $transactionDto) {
            $walletIds[] = $transactionDto->getWalletId();

            $cases .= sprintf(
                " WHEN id = '%s' THEN '%s'",
                $transactionDto->getWalletId(),
                $transactionDto->getAmount()->format()->asMinorUnit()
            );
        }

        $buildQuery = $this->wallet
            ->getConnection()
            ->raw(
                value: "CASE $cases END"
            );

        $this->wallet->newQuery()
            ->whereIn($this->wallet->getQualifiedKeyName(), array_values($walletIds))
            ->update([
                'balance' => $buildQuery
            ]);

        return array_map(
            fn (TransactionDto $transactionDto): int|string => $transactionDto->getWalletId(),
            $transactionDtos
        );
    }

    /**
     * @param array<string, int|string> $walletIds
     * @return Wallet[]
     */
    public function findById(array $walletIds): array
    {
        return $this->wallet->newQuery()
            ->whereIn('id', $walletIds)
            ->get()
            ->all();
    }
}
