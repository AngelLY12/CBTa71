<?php

namespace App\Models\traits;

use App\Models\User;

trait ResolvesTargetUser
{
    public function resolveTargetUser(?int $id = null): ?User
    {
        if ($this->hasRole('parent') && $id) {
            return $this->children()
                        ->where('student_id', $id)
                        ->with('student')
                        ->first()?->student;
        }

        return $this;
    }
}
