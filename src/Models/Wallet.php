<?php

declare(strict_types=1);

namespace Hennest\Wallet\Models;

use Hennest\Money\Casts\MoneyCast;
use Hennest\Wallet\Interfaces\WalletInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Wallet extends Model implements WalletInterface
{
    use HasUlids;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'slug',
        'description',
        'meta',
        'balance',
        'decimal_places',
    ];

    protected $casts = [
        'balance' => MoneyCast::class,
        'decimal_places' => 'int',
        'meta' => 'json',
    ];

    protected $attributes = [
        'balance' => 0,
        'decimal_places' => 2,
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
