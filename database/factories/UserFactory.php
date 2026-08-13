<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => implode(' ', [$this->faker->firstName, $this->faker->lastName]),
            'email' => $this->faker->safeEmail,
            'email_verified_at' => now(),
            'github_name' => $this->faker->userName,
            'points' => $this->faker->numberBetween(0, 1000),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'remember_token' => \Str::random(10),
            'is_admin' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(['is_admin' => true]);
    }

    public function regular(): static
    {
        return $this->state(['is_admin' => false]);
    }
}
