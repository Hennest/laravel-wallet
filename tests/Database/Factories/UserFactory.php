<?php

declare(strict_types=1);

namespace Hennest\Wallet\Tests\Database\Factories;

use Hennest\Wallet\Tests\Database\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'email' => fake()->unique()->safeEmail,
        ];
    }
}
