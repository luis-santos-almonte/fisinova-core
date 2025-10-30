<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Position;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        echo "🔍 Verificando roles y positions...\n";

        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $medicRole = Role::where('name', 'medic')->first();
        $therapistRole = Role::where('name', 'therapist')->first();
        $secretaryRole = Role::where('name', 'secretary')->first(); // ✅ NUEVO

        // Get positions
        $adminPosition = Position::where('name', 'Administrador')->first();
        $medicPosition = Position::where('name', 'Médico')->first();
        $therapistPosition = Position::where('name', 'Terapista')->first();
        $secretaryPosition = Position::where('name', 'Secretaria')->first(); // ✅ NUEVO

        // Verificar que existan
        if (!$adminRole || !$medicRole || !$therapistRole || !$secretaryRole) {
            echo "❌ Faltan roles\n";
            return;
        }
        if (!$adminPosition || !$medicPosition || !$therapistPosition || !$secretaryPosition) {
            echo "❌ Faltan positions\n";
            return;
        }

        echo "✅ Todos los roles y positions encontrados\n";

        // 1. ADMIN USER
        $admin = User::updateOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@system.com',
                'password' => Hash::make('admin123'),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'user_id' => $admin->id,
                'position_id' => $adminPosition->id,
                'firstname' => 'Administrator',
                'lastname' => 'System',
                'dni' => '00000000',
                'email' => 'admin@system.com',
                'active' => true,
            ]
        );

        // 2. MEDIC USER
        $medic = User::updateOrCreate(
            ['email' => 'fulanitodoc@system.com'],
            [
                'name' => 'Fulanito',
                'email' => 'fulanitodoc@system.com',
                'password' => Hash::make('admin123'),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $medic->id],
            [
                'user_id' => $medic->id,
                'position_id' => $medicPosition->id,
                'firstname' => 'Fulanito',
                'lastname' => 'de Tal',
                'dni' => '04701554389',
                'email' => 'fulanitodoc@system.com',
                'active' => true,
            ]
        );

        // 3. THERAPIST USER
        $therapist = User::updateOrCreate(
            ['email' => 'layiyi@system.com'],
            [
                'name' => 'Yesenia',
                'email' => 'layiyi@system.com',
                'password' => Hash::make('admin123'),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $therapist->id],
            [
                'user_id' => $therapist->id,
                'position_id' => $therapistPosition->id,
                'firstname' => 'Yesenia',
                'lastname' => 'Lopez',
                'dni' => '40125849858',
                'email' => 'layiyi@system.com',
                'active' => true,
            ]
        );

        // 4. SECRETARY USER ✅ NUEVO
        $secretary = User::updateOrCreate(
            ['email' => 'secretary@system.com'],
            [
                'name' => 'Maria',
                'email' => 'secretary@system.com',
                'password' => Hash::make('admin123'),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $secretary->id],
            [
                'user_id' => $secretary->id,
                'position_id' => $secretaryPosition->id,
                'firstname' => 'Maria',
                'lastname' => 'Gomez',
                'dni' => '40200000001',
                'email' => 'secretary@system.com',
                'active' => true,
            ]
        );

        // Assign roles
        $admin->roles()->syncWithoutDetaching([$adminRole->id => ['active' => true]]);
        $medic->roles()->syncWithoutDetaching([$medicRole->id => ['active' => true]]);
        $therapist->roles()->syncWithoutDetaching([$therapistRole->id => ['active' => true]]);
        $secretary->roles()->syncWithoutDetaching([$secretaryRole->id => ['active' => true]]); // ✅ NUEVO

        echo "✅ Usuarios creados exitosamente:\n";
        echo "   - admin@system.com (Admin)\n";
        echo "   - fulanitodoc@system.com (Médico)\n";
        echo "   - layiyi@system.com (Terapista)\n";
        echo "   - secretary@system.com (Secretaria)\n"; // ✅ NUEVO
    }
}