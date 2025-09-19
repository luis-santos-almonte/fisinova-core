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
        // Get roles and positions
        $adminRole = Role::where('name', 'admin')->first();
        $medicRole = Role::where('name', 'medic')->first();
        $therapistRole = Role::where('name', 'therapist')->first();

        $adminPosition = Position::where('name', 'Administrador')->first();
        $medicPosition = Position::where('name', 'Médico')->first();
        $therapistPosition = Position::where('name', 'Terapista')->first();

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

        $medic = User::updateOrCreate(
            ['email' => 'medic@system.com'],
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

        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id => ['active' => true]]);
        }
        if ($medicRole) {
            $medic->roles()->syncWithoutDetaching([$medicRole->id => ['active' => true]]);
        }
        if ($therapistRole) {
            $therapist->roles()->syncWithoutDetaching([$therapistRole->id => ['active' => true]]);
        }
    }
}
