<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'firstname'     => 'Super',
                'lastname'      => 'Admin',
                'phone_number'  => '0932483667',
                'phone_verified_at'=> now(),
                'password'      => Hash::make('adminadmin'),
                'date_of_birth' => '1990-01-01',
                'picture'       => 'default.png',
                'id_card_image' => 'default_id.png',
                'is_approved'   => true,
                'role'          => 'admin',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id' => 2,
                'firstname'     => 'John',
                'lastname'      => 'Owner',
                'phone_number'  => '0949378835',
                'phone_verified_at'=> now(),
                'password'      => Hash::make('123456789'),
                'date_of_birth' => '1992-05-10',
                'picture'       => 'default.png',
                'id_card_image' => 'default_id.png',
                'is_approved'   => true,
                'role'          => 'apartment_owner',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id' => 3,
                'firstname'     => 'Sara',
                'lastname'      => 'Renter',
                'phone_number'  => '0912345678',
                'phone_verified_at'=> now(),
                'password'      => Hash::make('123456789'),
                'date_of_birth' => '1995-09-20',
                'picture'       => 'default.png',
                'id_card_image' => 'default_id.png',
                'is_approved'   => true,
                'role'          => 'renter',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);

    }
}
