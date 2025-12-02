<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Damascus_cities = [
            'Dhadeel', 'Jaramana', 'Al-mazza', 'Bagdad.S', 'Baramika', 'Midan',
        ];
        foreach ($Damascus_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 1]);
        }

        $Homs_cities = [
            'Al-Hadara', 'Al-Ghota', 'Al-Mokhaiam', 'Al-Khalidia', 'Al-Waleed', 'Al-Shammas',
        ];
        foreach ($Homs_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 2]);
        }

        $Idlib_cities = [
            'Ariha', 'Maarrat al-Numan', 'Saraqib', 'Jisr al-Shughur',
        ];
        foreach ($Idlib_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 3]);
        }
    }
}
