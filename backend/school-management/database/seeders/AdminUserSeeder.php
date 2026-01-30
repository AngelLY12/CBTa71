<?php

namespace Database\Seeders;

use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Enum\User\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'lopezyanezangell@gmail.com'],
            [
                'name' => 'Angel',
                'last_name' => 'Lopez Yáñez',
                'email' => 'lopezyanezangell@gmail.com',
                'password' => Hash::make(config('auth.admin_password')),
                'phone_number' => '+527352770097',
                'birthdate' => '2003-05-04',
                'gender' => UserGender::HOMBRE,
                'curp' => 'LOYA030504HMSPXNA8',
                'address' => [
                    'street' => 'Calle Falsa 123',
                    'city' => 'Ciudad de Ejemplo',
                    'state' => 'Estado Ejemplo',
                    'zip' => '12345'
                ],
                'stripe_customer_id' => null,
                'blood_type' => UserBloodType::O_POSITIVE,
                'registration_date' => now()->toDateString(),
                'status' => UserStatus::ACTIVO,
            ]
        );

        $admin->syncRoles([UserRoles::ADMIN->value]);
    }
}
