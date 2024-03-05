<?php

declare(strict_types=1);

namespace Hennest\Wallet\Models\Traits;

use Hennest\Wallet\Models\Wallet;
use Hennest\Wallet\Repository\WalletRepository;
use Hennest\Wallet\Services\WalletService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWallets
{
    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'owner');
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
        return app(WalletService::class)->createWallet(
            model: $this,
            attributes: $attributes
        );
    }

    public function getWallet(string $slug): Wallet|null
    {
        return app(WalletRepository::class)->findBySlug(
            owner: $this,
            slug: $slug
        );
    }

    /**
     * @param string[] $slugs
     * @return Collection<int, Wallet>
     */
    public function getWallets(array $slugs): Collection
    {
        return app(WalletRepository::class)->getBySlugs(
            owner: $this,
            slugs: $slugs
        );
    }
}
