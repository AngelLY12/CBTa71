<?php

namespace Database\Seeders;

use App\Core\Domain\Enum\User\UserRoles;
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
        $createdRoles = [];

        foreach (UserRoles::values() as $roleName) {
            $attributes = ['name' => $roleName, 'guard_name' => 'sanctum'];

            if ($roleName === UserRoles::ADMIN->value) {
                $attributes['hidden'] = true;
            }

            $createdRoles[$roleName] = Role::updateOrCreate($attributes);
        }

        $studentPaymentPermissions = Permission::where('belongs_to', UserRoles::STUDENT->value . '-payment')
            ->where('type', 'role')
            ->get();
        $staffPermissions = Permission::where('belongs_to', UserRoles::FINANCIAL_STAFF->value)
            ->where('type', 'role')
            ->get();

        $globalPermissions = Permission::where('belongs_to', 'global-payment')
            ->where('type', 'role')
            ->get();

        $adminPermissions = Permission::where('belongs_to', 'administration')
            ->where('type', 'model')
            ->get();

        $createdRoles[UserRoles::STUDENT->value]->syncPermissions($studentPaymentPermissions->merge($globalPermissions));
        $createdRoles[UserRoles::FINANCIAL_STAFF->value]->syncPermissions($staffPermissions->merge($globalPermissions));
        $createdRoles[UserRoles::PARENT->value]->syncPermissions($studentPaymentPermissions->merge($globalPermissions));
        $createdRoles[UserRoles::ADMIN->value]->syncPermissions($adminPermissions);
        $createdRoles[UserRoles::APPLICANT->value]->syncPermissions($studentPaymentPermissions->merge($globalPermissions));
    }
}
