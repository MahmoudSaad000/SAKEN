<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookings>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = [
            'pending', // waiting for the owner to confirme the booking request
            'confirmed', // owner confirm client request
            'rejected', // owner reject client request
            'cancelled', // client cancel his/her request
            'completed', // client checked out and the booking completed
            'expired', // owner does not confirm client request and the time is up
            'payment_pending', // waiting for the client to pay
            'payment_failed', // something in payment went wrong
            'no_show', // client pay and haven't checked in
            'checked_in', // client arraived to appartment
            'modified' // client modifie his request
        ];


        return [
            //
            'renter_id' => User::inRandomOrder()->first()->id,
            'apartment_id' => Apartment::inRandomOrder()->first()->id,
            'check_in_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'check_out_date' => $this->faker->dateTimeBetween('+3 months', '+6 months'),
            'booking_status' => $this->faker->randomElement($statuses),
            'payment_method' => $this->faker->randomElement(['credit','bank_transfer','cash','digital_wallet']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
