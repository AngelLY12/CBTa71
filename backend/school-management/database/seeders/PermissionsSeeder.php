<?php

namespace Database\Seeders;

use App\Core\Domain\Enum\User\UserRoles;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
         $permissionsStudent = [
            'role' =>[
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
                'create payout'
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
            'view roles',
            'create user',
            'view student',
            'update student',
            'promote student'
        ];

        $globalPayment=[
            'refresh all dashboard',
            'view concept',
            'view payment',
        ];

        $insertPermissions = function (array $permissions, string $type='role', ?string $belongsTo=null) {
            $now = now();
            $existing = Permission::whereIn('name', $permissions)
                ->where('belongs_to', $belongsTo)
                ->where('type', $type)
                ->where('guard_name', 'web')
                ->pluck('name')
                ->toArray();

            $newPermissions = array_diff($permissions, $existing);

            $data = array_map(fn($name) => [
                'name' => $name,
                'guard_name' => 'web',
                'type' => $type,
                'belongs_to' => $belongsTo,
                'created_at' => $now,
                'updated_at' => $now,
            ], $newPermissions);

            DB::table('permissions')->insert($data);
        };

        $insertPermissions(array_merge($permissionsStudent['role']), 'role', UserRoles::STUDENT->value . '-payment');
        $insertPermissions(array_merge($permissionsStudent['model']), 'model', UserRoles::STUDENT->value . '-payment');
        $insertPermissions(array_merge($permissionsStaff['role']), 'role', UserRoles::FINANCIAL_STAFF->value);
        $insertPermissions(array_merge($permissionsStaff['model']), 'model', UserRoles::FINANCIAL_STAFF->value);

        $insertPermissions($globalPayment, 'role', 'global-payment');

        $insertPermissions($permissionsAdmin, 'model', 'administration');
    }
}
