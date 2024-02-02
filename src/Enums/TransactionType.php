<?php

declare(strict_types=1);

namespace Modules\Wallet\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';

    case Withdraw = 'withdraw';
}
