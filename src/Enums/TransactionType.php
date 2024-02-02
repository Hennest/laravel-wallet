<?php

declare(strict_types=1);

namespace Hennest\Wallet\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';

    case Withdraw = 'withdraw';
}
