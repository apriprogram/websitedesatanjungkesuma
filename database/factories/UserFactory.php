<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'nomor_hp' => fake()->numerify('08##########'),
            'gambar' => null,
            'is_active' => true,
            'is_admin' => true,
            'last_login' => null,
            'last_login_ip' => null,
            'last_login_location' => null,
            'remember_token' => Str::random(10),
        ];
    }
}

