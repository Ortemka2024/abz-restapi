<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // We use already prepared images because
        // the code in WSL takes a long time to work
        $photos = [
            'photo_1.jpg',
            'photo_2.jpg',
            'photo_3.jpg',
            'photo_4.jpg',
        ];

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->userName().'@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '+380'.$this->faker->unique()->numberBetween(100000000, 999999999),
            'position_id' => Position::exists() ? Position::inRandomOrder()->value('id') : null,
            'photo' => $this->faker->randomElement($photos),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
