<?php

declare(strict_types=1);

namespace Hennest\Wallet\Tests\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class User extends Model
{
    use HasUlids;

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'email'
    ];
}
