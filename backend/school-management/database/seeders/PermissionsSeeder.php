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
            'role' => [
                'view.own.pending.concepts.summary',
                'view.own.paid.concepts.summary',
                'view.own.overdue.concepts.summary',
                'view.payments.history',
                'view.cards',
                'view.payment',
                'view.payment.history',
                'view.pending.concepts',
                'view.overdue.concepts',
            ],
            'model' => [
                'create.setup',
                'delete.card',
                'create.payment',
            ],
        ];

        $permissionsStaff = [
            'role' => [
                'view.all.pending.concepts.summary',
                'view.all.students.summary',
                'view.all.paid.concepts.summary',
                'view.concepts.history',
                'view.concepts',
                'view.debts',
                'view.payments',
            ],
            'model' => [
                'create.concepts',
                'update.concepts',
                'finalize.concepts',
                'disable.concepts',
                'eliminate.concepts',
                'activate.concepts',
                'eliminate.logical.concepts',
                'validate.debt',
                'view.students',
                'view.stripe.payments',
                'create.payout'
            ],
        ];

        $permissionsAdmin = [
            'attach.student',
            'import.users',
            'sync.permissions',
            'view.users',
            'sync.roles',
            'activate.users',
            'disable.users',
            'delete.users',
            'view.permissions',
            'view.roles',
            'create.user',
            'view.student',
            'update.student',
            'promote.student'
        ];

        $globalPayment = [
            'refresh.all.dashboard',
        ];

        $insertPermissions = fn(array $permissions, string $type='role', ?string $belongsTo=null) =>
            collect($permissions)->each(fn($name) =>
                Permission::updateOrCreate(
                    ['name' => $name, 'guard_name' => 'sanctum'],
                    ['type' => $type, 'belongs_to' => $belongsTo]
                )
            );


        $insertPermissions(array_merge($permissionsStudent['role']), 'role', UserRoles::STUDENT->value . '-payment');
        $insertPermissions(array_merge($permissionsStudent['model']), 'model', UserRoles::STUDENT->value . '-payment');
        $insertPermissions(array_merge($permissionsStaff['role']), 'role', UserRoles::FINANCIAL_STAFF->value);
        $insertPermissions(array_merge($permissionsStaff['model']), 'model', UserRoles::FINANCIAL_STAFF->value);

        $insertPermissions($globalPayment, 'role', 'global-payment');

        $insertPermissions($permissionsAdmin, 'model', 'administration');
    }
}
