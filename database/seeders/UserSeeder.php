<?php
// database/seeders/BasicUserSeeder.php (o UserSeeder.php)

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

        // Get positions - NOMBRES EXACTOS de tu PositionSeeder actual
        $adminPosition = Position::where('name', 'Administrador')->first();
        $medicPosition = Position::where('name', 'Médico')->first(); // ✅ Coincide exacto
        $therapistPosition = Position::where('name', 'Terapista')->first(); // ✅ Coincide exacto

        // Verificar que existan
        if (!$adminRole) {
            echo "❌ Role 'admin' no encontrado\n";
            return;
        }
        if (!$medicRole) {
            echo "❌ Role 'medic' no encontrado\n";
            return;
        }
        if (!$therapistRole) {
            echo "❌ Role 'therapist' no encontrado\n";
            return;
        }
        if (!$adminPosition) {
            echo "❌ Position 'Administrador' no encontrado\n";
            return;
        }
        if (!$medicPosition) {
            echo "❌ Position 'Médico' no encontrado\n";
            return;
        }
        if (!$therapistPosition) {
            echo "❌ Position 'Terapista' no encontrado\n";
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

        $adminEmployee = Employee::updateOrCreate(
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

        $medicEmployee = Employee::updateOrCreate(
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

        $therapistEmployee = Employee::updateOrCreate(
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

        // Assign roles
        $admin->roles()->syncWithoutDetaching([$adminRole->id => ['active' => true]]);
        $medic->roles()->syncWithoutDetaching([$medicRole->id => ['active' => true]]);
        $therapist->roles()->syncWithoutDetaching([$therapistRole->id => ['active' => true]]);

        echo "✅ Usuarios creados exitosamente:\n";
        echo "   - admin@system.com (Admin)\n";
        echo "   - fulanitodoc@system.com (Médico)\n";
        echo "   - layiyi@system.com (Terapista)\n";
    }
}