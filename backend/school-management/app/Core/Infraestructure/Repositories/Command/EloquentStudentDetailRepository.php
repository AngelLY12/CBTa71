<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Entities\StudentDetail;
use App\Models\StudentDetail as EloquentStudentDetail;
use App\Core\Domain\Repositories\Command\StudentDetailReInterface;
use App\Core\Infraestructure\Mappers\StudentDetailMapper;

class EloquentStudentDetailRepository implements StudentDetailReInterface
{
    public function findStudentDetails(User $user): StudentDetail
    {
        $eloquentStudentDetails = EloquentStudentDetail::where('user_id',$user->id);
        return StudentDetailMapper::toDomain($eloquentStudentDetails);
    }
}
