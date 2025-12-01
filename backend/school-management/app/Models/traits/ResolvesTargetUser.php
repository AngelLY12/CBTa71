<?php

namespace App\Models\traits;

use App\Core\Domain\Enum\User\UserRoles;
use App\Models\User;

trait ResolvesTargetUser
{
    public function resolveTargetUser(?int $id = null): ?User
    {
        if ($this->hasRole(UserRoles::PARENT->value) && $id) {
            return $this->children()
                        ->where('student_id', $id)
                        ->with('student')
                        ->first()?->student;
        }

        return $this;
    }
}
