<?php

declare(strict_types=1);

namespace Hennest\Wallet\Models\Traits;

use Hennest\Wallet\Models\Wallet;
use Hennest\Wallet\Services\WalletService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWallets
{
    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'owner');
    }

    public function createWallet(array $attributes): Wallet
    {
        $wallet = app(WalletService::class)->createWallet($attributes);
        $this->save();

        return $wallet;
    }
}
