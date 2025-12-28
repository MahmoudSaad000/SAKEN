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
            'id' => 1,
            'firstname'     => 'Super',
            'lastname'      => 'Admin',
            'phone_number'  => '0932483667', // adjust to your format
            'password'      => Hash::make('adminadmin'), // secure hash
            'date_of_birth' => '1990-01-01',
            'picture'       => 'default.png',
            'id_card_image' => 'default_id.png',
            'is_approved'   => true,
            'role'          => 'admin',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
