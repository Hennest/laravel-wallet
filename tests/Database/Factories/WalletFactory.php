<?php

declare(strict_types=1);

namespace Hennest\Wallet\Tests\Database\Factories;

use Hennest\Money\Money;
use Hennest\Wallet\Tests\Database\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

final class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'holder_id' => fake()->uuid,
            'holder_type' => fake()->name,
            'name' => fake()->name,
            'slug' => fake()->slug,
            'balance' => new Money(
                fake()->randomNumber(4, true),
            )
        ];
    }
}
