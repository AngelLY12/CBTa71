<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentRole=Role::firstOrCreate(['name' => 'student']);
        $staffRole=Role::firstOrCreate(['name' => 'financial staff']);
        $parentRole= Role::firstOrCreate(['name' => 'parent']);
        Role::firstOrCreate(['name'=> 'unverified']);
        Role::firstOrCreate(['name' => 'admin']);

        $studentPermissions = Permission::where('belongs_to', 'student')->get();
        $staffPermissions = Permission::where('belongs_to', 'financial staff')->get();
        $globalPermissions = Permission::where('belongs_to','global')->get();

        $studentRole->syncPermissions($studentPermissions->merge($globalPermissions));
        $staffRole->syncPermissions($staffPermissions->merge($globalPermissions));
        $parentRole->syncPermissions($studentPermissions->merge($globalPermissions));
    }
}
