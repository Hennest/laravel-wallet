<?php

declare(strict_types=1);

namespace Hennest\Wallet\Repository;

use Hennest\Money\Money;
use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Wallet;
use Hennest\Wallet\Services\CastService;
use Hennest\Wallet\Services\ConsistencyService;
use Illuminate\Database\Eloquent\Collection;

final readonly class WalletRepository
{
    public function __construct(
        private Wallet $wallet,
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
    public function create(array $attributes): Wallet
    {
        $wallet = $this->wallet->newInstance($attributes);

        $wallet->saveQuietly();

        return $wallet;
    }

    public function updateBalance(Wallet $wallet, Money $amount): Wallet
    {
        $wallet->fill([
            'balance' => $amount
        ]);

        $wallet->saveQuietly();

        return $wallet;
    }

    /**
     * @param array<int, Wallet> $wallets
     * @param array<int, Money> $amounts
     * @return array<int, int|string>
     */
    public function updateBalances(array $wallets, array $amounts): array
    {
        $this->consistencyService->ensureConsistency(
            wallets: $wallets,
            amounts: $amounts
        );

        $cases = '';
        $walletIds = [];

        foreach ($wallets as $key => $wallet) {
            $walletIds[] = $wallet->getKey();

            $cases .= sprintf(
                " WHEN id = '%s' THEN '%s'",
                $wallet->getKey(),
                $amounts[$key]->format()->asMinorUnit()
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
            fn (Wallet $wallet): int|string => $wallet->getKey(),
            $wallets
        );
    }

    /**
     * @param array<string, int|string> $walletIds
     * @return Wallet[]
     */
    public function findByIds(array $walletIds): array
    {
        return $this->wallet->newQuery()
            ->whereIn('id', $walletIds)
            ->get()
            ->all();
    }

    public function findBySlug(WalletInterface $owner, string $slug): Wallet|null
    {
        $owner = app(CastService::class)->getOwner($owner);

        return $this->findBy([
            'owner_id' => $owner->getKey(),
            'owner_type' => $owner->getMorphClass(),
            'slug' => $slug,
        ]);
    }

    /**
     * @param array{
     *     id: string,
     *     owner_id: string,
     *     owner_type: string,
     *     slug?: string,
     * } $attributes
     */
    public function findBy(array $attributes): Wallet|null
    {
        return $this->wallet->newQuery()
            ->where($attributes)
            ->first();
    }

    /**
     * @param string[] $slugs
     * @return Collection<int, Wallet>
     */
    public function getBySlugs(WalletInterface $owner, array $slugs): Collection
    {
        return $this->wallet->newQuery()
            ->where([
                'owner_id' => $owner->getKey(),
                'owner_type' => $owner->getMorphClass(),
            ])
            ->whereIn('slug', $slugs)
            ->get();
    }
}
