<?php

declare(strict_types=1);

namespace Modules\Wallet\DTOs;

final class WalletDto
{
    public function __construct(
        public string $key,
        public OwnerDto $owner,
    ) {
    }
}
