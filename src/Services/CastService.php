<?php

declare(strict_types=1);

namespace Hennest\Wallet\Services;

use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

final class CastService
{
    public function getWallet(WalletInterface $wallet): Wallet
    {
        if ( ! $wallet instanceof Wallet) {
            return $wallet->getAttribute('wallet');
        }

        return $wallet;
    }

    public function getOwner(Model|WalletInterface $wallet): Model|WalletInterface
    {
        return $wallet instanceof Wallet
            ? $wallet->getAttribute('owner')
            : $wallet;
    }
}
