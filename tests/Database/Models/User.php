<?php

declare(strict_types=1);

namespace Hennest\Wallet\Tests\Database\Models;

use Hennest\Wallet\Interfaces\WalletInterface;
use Hennest\Wallet\Models\Traits\HasWallet;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class User extends Model implements WalletInterface
{
    use HasUlids;
    use HasWallet;

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'email'
    ];

    public function wallet(): MorphOne
    {
        return $this->morphOne(\Hennest\Wallet\Models\Wallet::class, 'owner');
    }
}
