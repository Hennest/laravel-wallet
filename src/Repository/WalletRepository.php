<?php

declare(strict_types=1);

namespace Hennest\Wallet\Repository;

use Hennest\Money\Money;
use Hennest\Wallet\Events\WalletCreatedEvent;
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
     * @param array<string, Money> $balances
     */
    public function updateBalances(array $balances): int
    {
        $cases = '';
        foreach ($balances as $walletId => $balance) {
            $cases .= "WHEN id = $walletId THEN {$balance->format()->asMinorUnit()}";
        }

        $buildQuery = $this->wallet
            ->getConnection()
            ->raw(
                value: "CASE $cases END"
            );

        $wallet = $this->wallet->newQuery()
            ->whereIn($this->wallet->getQualifiedKeyName(), array_keys($balances))
            ->update([
                'balance' => $buildQuery
            ]);

        event(new WalletCreatedEvent(
            id: $this->wallet->id,
            ownerId: $this->wallet->owner_id,
            ownerType: $this->wallet->owner_type
        ));

        return $wallet;
    }
}
