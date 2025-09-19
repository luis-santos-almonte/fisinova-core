<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Insurance;

class InsuranceSeeder extends Seeder
{
    public function run(): void
    {
        $insurances = [
            ['name' => 'SENASA', 'provider_code' => 'SENASA', 'active' => true],
            ['name' => 'ARS Humano', 'provider_code' => 'HUMANO', 'active' => true],
            ['name' => 'ARS Universal', 'provider_code' => 'UNIVERSAL', 'active' => true],
            ['name' => 'Seguros Reservas', 'provider_code' => 'RESERVAS', 'active' => true],
            ['name' => 'Particular', 'provider_code' => 'PARTICULAR', 'active' => true],
        ];

        foreach ($insurances as $insurance) {
            Insurance::updateOrCreate(['name' => $insurance['name']], $insurance);
        }
    }
}