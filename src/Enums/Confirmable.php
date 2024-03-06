<?php

declare(strict_types=1);

namespace Hennest\Wallet\Enums;

interface Confirmable
{
    public function isConfirmed(): bool;
}
