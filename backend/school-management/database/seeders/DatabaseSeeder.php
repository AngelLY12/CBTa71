<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ------------------------
        // PERMISOS DE STUDENT
        // ------------------------
        $studentPermissions = [
            'view own financial overview',
            'view own pending concepts summary',
            'view own paid concepts summary',
            'view own overdue concepts summary',
            'view payments history',
            'view cards',
            'create setup',
            'create and view card',
            'delete card',
            'view payment history',
            'view pending concepts',
            'create payment',
            'view overdue concepts',
        ];

        foreach ($studentPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ------------------------
        // PERMISOS DE FINANCIAL STAFF
        // ------------------------
        $staffPermissions = [
            'view all financial overview',
            'view all pending concepts summary',
            'view all students summary',
            'view all paid concepts summary',
            'view concepts history',
            'view concepts',
            'create concepts',
            'update concepts',
            'finalize concepts',
            'disable concepts',
            'eliminate concepts',
            'view debts',
            'validate debt',
            'view payments',
            'view students',
        ];

        foreach ($staffPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ------------------------
        // CREAR ROLES Y ASIGNAR PERMISOS
        // ------------------------
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->syncPermissions($studentPermissions);

        $staffRole = Role::firstOrCreate(['name' => 'financial staff']);
        $staffRole->syncPermissions($staffPermissions);
    }

}
