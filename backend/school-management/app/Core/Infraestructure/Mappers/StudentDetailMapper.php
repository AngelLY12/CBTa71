<?php

namespace App\Core\Infraestructure\Mappers;

use App\Core\Domain\Entities\StudentDetail as DomainStudentDetail;
use App\Models\StudentDetail;

class StudentDetailMapper{

    public static function toDomain(StudentDetail $studentDetail): DomainStudentDetail
    {
        return new DomainStudentDetail(
            id:$studentDetail->id,
            user_id:$studentDetail->user_id,
            career_id:$studentDetail->career_id,
            n_control:$studentDetail->n_control,
            semestre:$studentDetail->semestre,
            group:$studentDetail->group,
            workshop:$studentDetail->workshop
        );

    }
}
