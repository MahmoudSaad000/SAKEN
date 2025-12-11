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
            "Qabr as Sitt",
            "Duma",
            "Jaramana",
            "Al Hajar al Aswad",
            "Darayya",
            "At Tall",
            "An Nabk",
            "Babila",
            "Yabrud",
            "Harasta",
            "Zamalka",
            "Al Kiswah",
            "Jayrud",
            "Al Ghizlaniyah",
            "Qadsayya",
            "Ar Ruhaybah",
            "Yalda",
            "Al Qutayfah",
            "Az Zabadani",
            "Siqba",
            "Saydnaya",
            "Qatana",
            "Kafr Batna",
            "`Adra",
            "Zakiyah",
            "Manin",
            "Bayt Saham",
            "Al Mu`addamiyah",
            "Sahnaya",
            "Kanakir",
            "Qarah",
            "Hawsh al Bahdaliyah",
            "Dayr `Atiyah",
            "As Sabburah",
            "`Utaybah",
            "Ma`raba",
            "Al Hamah",
            "Jisrayn",
            "Madaya",
            "Al Buwaydah",
            "Ra's al Ma`arrah"
        ];

        foreach ($Damascus_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 1]);
        }

        $Homs_cities = [
            "Ar Rastan",
            "Tadmur",
            "Al Qusayr",
            "Tallbisah",
            "Al Qaryatayn",
            "Tallkalakh",
            "Kafr Laha",
            "As Sukhnah",
            "Shin",
            "Tall Dhahab",
            "Mahin"
        ];
        foreach ($Homs_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 2]);
        }

        $Idlib_cities = [
            "Ma`arrat an Nu`man",
            "Khan Shaykhun",
            "Jisr ash Shughur",
            "Saraqib",
            "Ma`arratmisrin",
            "Kafr Nubl",
            "Salqin",
            "Harim",
            "Binnish",
            "Sarmada",
            "Sarmin",
            "Kafr Ruma",
            "Jarjanaz",
            "Turmanin",
            "Barah",
            "Armanaz",
            "Kafr Takharim",
            "Has",
            "Kafr Sajnah",
            "Hish",
            "Taftanaz"
        ];

        foreach ($Idlib_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 3]);
        }
    }
}
