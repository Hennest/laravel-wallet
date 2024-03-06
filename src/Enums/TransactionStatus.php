<?php

declare(strict_types=1);

namespace Hennest\Wallet\Enums;

enum TransactionStatus: string implements Confirmable
{
    case Pending = 'pending';

    case Confirmed = 'confirmed';

    public function isConfirmed(): bool
    {
        return self::Confirmed === $this;
    }
}
