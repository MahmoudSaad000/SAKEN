<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Apartment>
 */
class ApartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'description' => $this->faker->sentence,
            'area' => $this->faker->numberBetween(30, 200),
            'rooms' => $this->faker->numberBetween(1, 5),
            'living_rooms' => $this->faker->numberBetween(1, 3),
            'bathrooms' => $this->faker->numberBetween(1, 2),
            'rental_price' => $this->faker->numberBetween(500, 2000),
            'address' => $this->faker->address,
            'status' => $this->faker->randomElement(['Booked', 'Free']),
            'average_rate' => $this->faker->randomFloat(2, 1, 5),
            'offer_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
