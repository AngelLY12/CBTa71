<?php

namespace Database\Seeders;

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
        $admin = User::updateOrCreate(
            ['email' => 'lopezyanezangell@gmail.com'],
            [
                'name' => 'Angel',
                'last_name' => 'Lopez Yáñez',
                'email' => 'lopezyanezangell@gmail.com',
                'password' => Hash::make('123'),
                'phone_number' => '7352770097',
                'birthdate' => '2003-05-04',
                'gender' => 'Hombre',
                'curp' => 'LOYA030504HMSPXNA8',
                'address' => [
                    'street' => 'Calle Falsa 123',
                    'city' => 'Ciudad de Ejemplo',
                    'state' => 'Estado Ejemplo',
                    'zip' => '12345'
                ],
                'stripe_customer_id' => null,
                'blood_type' => 'O+',
                'registration_date' => now()->toDateString(),
                'status' => 'activo',
            ]
        );
        $admin->assignRole('admin');
        $admin->givePermissionTo('attach student');
        $admin->givePermissionTo('import users');
        $admin->givePermissionTo('sync permissions');
    }
}
