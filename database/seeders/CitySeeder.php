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

         $Hama_cities = [
           'Masyaf', 'Al-Salamiyah', 'Mhardeh', 'Al-Suqaylabiyah'
        ];
        foreach ($Hama_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 4]);
        }


         $Aleppo_cities = [
           'Azaz', 'Manbij', 'Al-Bab', 'Jarabulus'
        ];
        foreach ($Aleppo_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 5]);
        }

        $Daraa_cities = [
           'Izra', 'Nawa', 'Jasim', 'Al-Sanamayn'
        ];
        foreach ($Daraa_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 6]);
        }

          $Qwnitra_cities = [
           'Khan Arnabah', 'Madīnat al-Baath', 'Jubbata al-Khashab', 'Al-Rafid'
        ];
        foreach ($Qwnitra_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 7]);
        }

        $Latakia_cities = [
           'Jableh', 'Al-Haffah', 'Qardaha', 'Rabia'
        ];
        foreach ( $Latakia_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 8]);
        }

         $Tartous_cities = [
           'Baniyas', 'Safita', 'Al-Sheikh Badr', 'Dreikish'
        ];
        foreach ( $Tartous_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 9]);
        }

         $DearAlZoor_cities = [
           'Al-Mayadin', 'Al-Bukamal', 'Al-Shaddadah', 'Al-Suwar'
        ];
        foreach ( $DearAlZoor_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 10]);
        }
         

         $Alswida_cities = [
          'Shahba', 'Salkhad', 'Al-Qurayya', 'Al-Mazraa'
        ];
        foreach ( $Alswida_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 11]);
        }
         $AlRaqa_cities = [
          'Al-Thawrah', 'Al-Sabkha', 'Maadan', 'Al-Karamah'
        ];
        foreach ( $AlRaqa_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 12]);
        }
         $AlHasaka_cities = [
          'Qamishli', 'Ras al-Ayn', 'Al-Malikiyah', 'Shaddadi'
        ];
        foreach ( $AlHasaka_cities as $city) {
            City::create(['name' => $city, 'governorate_id' => 13]);
        }
    }
}
