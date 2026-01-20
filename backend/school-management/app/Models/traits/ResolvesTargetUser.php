<?php

namespace App\Models\traits;

use App\Core\Domain\Enum\User\UserRoles;
use App\Models\User;

trait ResolvesTargetUser
{
    public function resolveTargetUser(?int $id = null): ?User
    {
        if ($this->hasRole(UserRoles::PARENT->value) && $id) {
            $child = $this->children()
                        ->where('student_id', $id)
                        ->with(['student' => function ($query) {
                            $query->with(['studentDetail', 'roles']);
                        }])
                        ->first();
            if (!$child) {
                return null;
            }

            return $child->student;
        }

        $this->loadMissing(['roles']);

        if($this->hasRole(UserRoles::STUDENT->value)){
            $this->loadMissing(['studentDetail']);
        }


        return $this;
    }
}
