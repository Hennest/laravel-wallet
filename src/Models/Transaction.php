<?php

declare(strict_types=1);

namespace Hennest\Wallet\Models;

use Hennest\Money\Casts\MoneyCast;
use Hennest\Wallet\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class Transaction extends Model
{
    use HasUlids;

    protected $fillable = [
        'id',
        'type',
        'wallet_id',
        'payable_id',
        'payable_type',
        'amount',
        'confirmed',
        'meta',
    ];

    protected $casts = [
        'confirmed' => 'bool',
        'meta' => 'array',
        'wallet_id' => 'string',
        'amount' => MoneyCast::class,
        'type' => TransactionType::class,
    ];
}
