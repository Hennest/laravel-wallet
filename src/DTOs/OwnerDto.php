<?php

declare(strict_types=1);

namespace Hennest\Wallet\DTOs;

final class OwnerDto
{
    public function __construct(
        public string $key,
    ) {
    }
}
