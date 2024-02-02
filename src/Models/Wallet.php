<?php

declare(strict_types=1);

namespace Modules\Wallet\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Money\Money;

final class Wallet extends Model
{
    public Money $balance;

    public User $owner;
}
