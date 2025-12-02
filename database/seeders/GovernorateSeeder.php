<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            'Damascus',
            'Homs',
            'Idlib',
            'Hama',
            'Aleppo',
            'Daraa',
            'Qwnitra',
            'Latakia',
            'Tartous',
            'Dear Al-Zoor',
            'Al-Swida',
            'Al-Raqa',
            'Al-Hasaka',
        ];
        foreach ($governorates as $governorate) {
            Governorate::create(['name' => $governorate]);
        }
    }
}
