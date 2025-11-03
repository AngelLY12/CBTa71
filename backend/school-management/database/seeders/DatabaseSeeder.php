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
        $permissions = [
            //permisos de alumnos
            'view own financial overview',
            'view own pending concepts summary',
            'view own paid concepts summary',
            'view own overdue concepts summary',
            'view payments history',
            'view cards',
            'create setup',
            'delete card',
            'view payment history',
            'view pending concepts',
            'create payment',
            'view overdue concepts',
            'refresh all dashboard',
            //permisos de staff
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
            'activate concept',
            'eliminate logical concept',
            'view debts',
            'validate debt',
            'view payments',
            'view students',
            'view stripe-payments',
            'refresh all dashboard',
            //permisos de admin
            'attach student',
            'import users',
            'sync permissions'
        ];

        foreach($permissions as $perm)
        {
            Permission::firstOrCreate(['name'=>$perm]);
        }

        // ------------------------
        // CREAR ROLES Y ASIGNAR PERMISOS
        // ------------------------
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'financial staff']);
        Role::firstOrCreate(['name' => 'admin']);

        $admin = $this->call(AdminUserSeeder::class);

    }

}
