<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Insurance;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $insurances = Insurance::all()->keyBy('name');

        if ($insurances->isEmpty()) {
            $this->command->error('❌ Ejecute primero InsuranceSeeder');
            return;
        }

        $patients = [
            [
                'firstname' => 'Roberto',
                'lastname' => 'García Martínez',
                'dni' => '00101234567',
                'sex' => 'male',
                'birthdate' => '1985-03-15',
                'email' => 'roberto.garcia@email.com',
                'phone' => '809-555-4001',
                'cellphone' => '829-555-4001',
                'address' => 'Calle Primera #123, Los Mina',
                'city' => 'Santo Domingo Este',
                'insurance_id' => $insurances->get('SENASA')?->id,
                'insurance_code' => 'SEN-123456',
                'active' => true,
            ],
            [
                'firstname' => 'María',
                'lastname' => 'Rodríguez Santos',
                'dni' => '00102345678',
                'sex' => 'female',
                'birthdate' => '1990-07-22',
                'email' => 'maria.rodriguez@email.com',
                'phone' => '809-555-4002',
                'cellphone' => '849-555-4002',
                'address' => 'Av. Venezuela #456, Gazcue',
                'city' => 'Santo Domingo',
                'insurance_id' => $insurances->get('ARS Humano')?->id,
                'insurance_code' => 'HUM-789012',
                'active' => true,
            ],
            [
                'firstname' => 'José',
                'lastname' => 'Pérez Valdez',
                'dni' => '00103456789',
                'sex' => 'male',
                'birthdate' => '1978-11-30',
                'email' => 'jose.perez@email.com',
                'phone' => '809-555-4003',
                'cellphone' => '829-555-4003',
                'address' => 'Calle Santomé #789, Ciudad Nueva',
                'city' => 'Santo Domingo',
                'insurance_id' => $insurances->get('ARS Universal')?->id,
                'insurance_code' => 'UNI-345678',
                'active' => true,
            ],
            [
                'firstname' => 'Carmen',
                'lastname' => 'López Fernández',
                'dni' => '00104567890',
                'sex' => 'female',
                'birthdate' => '1995-05-18',
                'email' => 'carmen.lopez@email.com',
                'phone' => '809-555-4004',
                'cellphone' => '849-555-4004',
                'address' => 'Calle Respaldo #321, Villa Mella',
                'city' => 'Santo Domingo Norte',
                'insurance_id' => $insurances->get('MAPFRE Salud')?->id,
                'insurance_code' => 'MAP-901234',
                'active' => true,
            ],
            [
                'firstname' => 'Luis',
                'lastname' => 'Martínez Ortiz',
                'dni' => '00105678901',
                'sex' => 'male',
                'birthdate' => '1982-09-25',
                'email' => 'luis.martinez@email.com',
                'phone' => '809-555-4005',
                'cellphone' => '829-555-4005',
                'address' => 'Av. España #654, Bella Vista',
                'city' => 'Santo Domingo',
                'insurance_id' => $insurances->get('Seguros Reservas')?->id,
                'insurance_code' => 'RES-567890',
                'active' => true,
            ],
            [
                'firstname' => 'Ana',
                'lastname' => 'Sánchez Díaz',
                'dni' => '00106789012',
                'passport' => 'DO1234567',
                'sex' => 'female',
                'birthdate' => '1988-12-10',
                'email' => 'ana.sanchez@email.com',
                'phone' => '809-555-4006',
                'cellphone' => '849-555-4006',
                'address' => 'Calle El Conde #987, Zona Colonial',
                'city' => 'Santo Domingo',
                'insurance_id' => $insurances->get('Particular')?->id,
                'insurance_code' => null,
                'active' => true,
            ],
            [
                'firstname' => 'Pedro',
                'lastname' => 'González Ramírez',
                'dni' => '00107890123',
                'sex' => 'male',
                'birthdate' => '1975-04-08',
                'email' => 'pedro.gonzalez@email.com',
                'phone' => '809-555-4007',
                'cellphone' => '829-555-4007',
                'address' => 'Calle Proyecto #147, Los Praditos',
                'city' => 'Santo Domingo Oeste',
                'insurance_id' => $insurances->get('ARS Monumental')?->id,
                'insurance_code' => 'MON-234567',
                'active' => true,
            ],
            [
                'firstname' => 'Rosa',
                'lastname' => 'Jiménez Castro',
                'dni' => '00108901234',
                'sex' => 'female',
                'birthdate' => '1992-08-14',
                'email' => 'rosa.jimenez@email.com',
                'phone' => '809-555-4008',
                'cellphone' => '849-555-4008',
                'address' => 'Av. Máximo Gómez #258, Ensanche La Fe',
                'city' => 'Santo Domingo',
                'insurance_id' => $insurances->get('Palic Salud')?->id,
                'insurance_code' => 'PAL-890123',
                'active' => true,
            ],
            [
                'firstname' => 'Miguel',
                'lastname' => 'Herrera Suárez',
                'dni' => '00109012345',
                'sex' => 'male',
                'birthdate' => '1987-06-20',
                'email' => 'miguel.herrera@email.com',
                'phone' => '809-555-4009',
                'cellphone' => '829-555-4009',
                'address' => 'Calle Luperón #369, Villa Consuelo',
                'city' => 'Santo Domingo',
                'insurance_id' => $insurances->get('La Colonial')?->id,
                'insurance_code' => 'COL-456789',
                'active' => true,
            ],
            [
                'firstname' => 'Laura',
                'lastname' => 'Méndez Torres',
                'dni' => '00110123456',
                'sex' => 'female',
                'birthdate' => '1993-02-28',
                'email' => 'laura.mendez@email.com',
                'phone' => '809-555-4010',
                'cellphone' => '849-555-4010',
                'address' => 'Calle Principal #741, Herrera',
                'city' => 'Santo Domingo',
                'insurance_id' => $insurances->get('ARS Yunen')?->id,
                'insurance_code' => 'YUN-123890',
                'active' => true,
            ],
        ];

        foreach ($patients as $patientData) {
            Patient::updateOrCreate(
                ['dni' => $patientData['dni']],
                $patientData
            );
        }

        $this->command->info('✅ Pacientes creados: 10 pacientes dominicanos');
    }
}
