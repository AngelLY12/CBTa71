<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        // PERMISOS
        // ------------------------
        $permissionsStudent = [
            //permisos de alumnos
            'role' =>[
                'view own financial overview',
                'view own pending concepts summary',
                'view own paid concepts summary',
                'view own overdue concepts summary',
                'view payments history',
                'view cards',
                'view payment history',
                'view pending concepts',
                'view overdue concepts',
            ],
            'model'=>[
                'create setup',
                'delete card',
                'create payment',
            ],
        ];

        $permissionsStaff=[
            'role' =>[
                'view all financial overview',
                'view all pending concepts summary',
                'view all students summary',
                'view all paid concepts summary',
                'view concepts history',
                'view concepts',
                'view debts',
                'view payments',

            ],
            'model' =>[
                'create concepts',
                'update concepts',
                'finalize concepts',
                'disable concepts',
                'eliminate concepts',
                'activate concept',
                'eliminate logical concept',
                'validate debt',
                'view students',
                'view stripe-payments',
            ],
        ];

        $permissionsAdmin=[
            'attach student',
            'import users',
            'sync permissions',
            'view users',
            'sync roles',
            'activate users',
            'disable users',
            'delete users',
            'view permissions',
            'view roles'
        ];

        $global=[
            'refresh all dashboard',
            'view concept',
            'view payment',
            'view profile',
        ];

         $insertPermissions = function (array $permissions, string $type='role') {
            DB::table('permissions')->insertOrIgnore(
                collect($permissions)->map(fn($name) => [
                    'name' => $name,
                    'guard_name' => 'web',
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray()
            );
        };

         $insertPermissions(array_merge(
            $permissionsStudent['role'],
            $permissionsStaff['role'],
            $global
        ),'role');

        $insertPermissions(array_merge(
            $permissionsStudent['model'],
            $permissionsStaff['model'],
            $permissionsAdmin,
        ),'model');

        $careers=
        [
            'Técnico Agropecuario',
            'Técnico en Informática',
            'Técnico en Administración para el Emprendimiento'
        ];

        foreach($careers as $career)
        {
            Career::firstOrCreate(['career_name'=>$career]);
        }

        // ------------------------
        // CREAR ROLES
        // ------------------------


        $studentRole=Role::firstOrCreate(['name' => 'student']);
        $staffRole=Role::firstOrCreate(['name' => 'financial staff']);
        Role::firstOrCreate(['name' => 'admin']);

        $studentRole->syncPermissions(array_merge(
            $permissionsStudent['role'],
            $global
        ));

        $staffRole->syncPermissions(array_merge(
            $permissionsStaff['role'],
            $global
        ));
        $this->call(AdminUserSeeder::class);

    }

}
