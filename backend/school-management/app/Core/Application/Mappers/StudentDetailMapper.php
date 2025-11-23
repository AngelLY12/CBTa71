<?php

namespace App\Core\Application\Mappers;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;

class StudentDetailMapper{

    public static function toCreateStudentDetailDTO(array $data): CreateStudentDetailDTO
    {
        return new CreateStudentDetailDTO(
            user_id: $data['user_id'],
            career_id: $data['career_id'],
            n_control:$data['n_control'],
            semestre: $data['semestre'],
            group: $data['group'],
            workshop:$data['workshop']
        );
    }


}
