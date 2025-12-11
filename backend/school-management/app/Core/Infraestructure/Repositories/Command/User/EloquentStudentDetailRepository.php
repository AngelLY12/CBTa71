<?php

namespace App\Core\Infraestructure\Repositories\Command\User;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Entities\StudentDetail;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Domain\Repositories\Command\User\StudentDetailReInterface;
use App\Models\StudentDetail as EloquentStudentDetail;
use App\Core\Infraestructure\Mappers\StudentDetailMapper;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Models\User as ModelsUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class EloquentStudentDetailRepository implements StudentDetailReInterface
{
    public function findStudentDetails(int $userId): ?StudentDetail
    {
        $eloquentStudentDetails = EloquentStudentDetail::where('user_id',$userId);
        return $eloquentStudentDetails ? StudentDetailMapper::toDomain($eloquentStudentDetails): null;
    }

    public function insertStudentDetails(array $studentDetails): void {
        if (!empty($studentDetails)) {
            DB::table('student_details')->insertOrIgnore($studentDetails);
        }
    }

    public function incrementSemesterForAll(): int
    {
        return EloquentStudentDetail::where('semestre', '<=', 10)
            ->whereHas('user', function ($query) {
                $query->whereIn('status', [UserStatus::ACTIVO, UserStatus::BAJA_TEMPORAL]);
            })
            ->increment('semestre');
    }

    public function getStudentsExceedingSemesterLimit(int $maxSemester = 10): array
    {
        return EloquentStudentDetail::where('semestre', '>', $maxSemester)
            ->whereHas('user', function ($query) {
                $query->whereIn('status', [UserStatus::ACTIVO, UserStatus::BAJA_TEMPORAL]);
            })
            ->pluck('user_id')
            ->toArray();
    }


    public function updateStudentDetails(int $user_id, array $fields): User
    {
        $model= $this->findModelByUserId($user_id);
        $model->update($fields);
        return UserMapper::toDomain($model->user);
    }

    public function attachStudentDetail(CreateStudentDetailDTO $detail, ModelsUser $user): User
    {
        $user->studentDetail()->create(
            StudentDetailMapper::toPersistence($detail)
        );
        $user->load('studentDetail');
        $user->syncRoles([UserRoles::STUDENT->value]);
        return UserMapper::toDomain($user);
    }

    private function findModelByUserId(int $user_id): EloquentStudentDetail
    {
        return EloquentStudentDetail::where('user_id', $user_id)->firstOr(
            throw new ModelNotFoundException('No se encontraron detalles de estudiante para este usuario')
        );
    }

}
