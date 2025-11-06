<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Insurance;

class InsuranceSeeder extends Seeder
{
    public function run(): void
    {
        $insurances = [
            // ARS principales de República Dominicana
            [
                'name' => 'Particular',
                'provider_code' => '0',
                'active' => true
            ],
            [
                'name' => 'SENASA',
                'provider_code' => '3993183',
                'active' => true
            ],
            [
                'name' => 'ARS Humano',
                'provider_code' => '313345',
                'active' => true
            ],
            [
                'name' => 'ARS Universal',
                'provider_code' => '63735',
                'active' => true
            ],
            [
                'name' => 'ARS Monumental',
                'provider_code' => '53255',
                'active' => true
            ],
            [
                'name' => 'Seguros Reservas',
                'provider_code' => '997653',
                'active' => true
            ],
            [
                'name' => 'MAPFRE Salud',
                'provider_code' => '614504833',
                'active' => true
            ],
            [
                'name' => 'Palic Salud',
                'provider_code' => '40548',
                'active' => true
            ],
            [
                'name' => 'La Colonial',
                'provider_code' => '55433',
                'active' => true
            ],
            [
                'name' => 'ARS Yunen',
                'provider_code' => '88776',
                'active' => true
            ],
            [
                'name' => 'ARS CMD',
                'provider_code' => '445522',
                'active' => true
            ],
        ];

        foreach ($insurances as $insurance) {
            Insurance::updateOrCreate(['name' => $insurance['name']], $insurance);
        }
    }
}
