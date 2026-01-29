<?php

namespace App\Core\Application\Services\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Response\General\ImportResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Admin\StudentManagement\AttachStudentDetailUserCase;
use App\Core\Application\UseCases\Admin\StudentManagement\BulkImportStudentDetailsUseCase;
use App\Core\Application\UseCases\Admin\StudentManagement\FindStudentDetailUseCase;
use App\Core\Application\UseCases\Admin\StudentManagement\UpdateStudentDeatilsUseCase;
use App\Core\Domain\Entities\StudentDetail;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\AdminCacheSufix;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Infraestructure\Cache\CacheService;

class AdminStudentServiceFacades
{
    use HasCache;
    private const TAG_USERS_ID = [CachePrefix::ADMIN->value, AdminCacheSufix::USERS->value, "all:id"];
    private const TAG_STUDENT_DETAILS = [CachePrefix::USER->value, "student-details"];
    public function __construct(
        private FindStudentDetailUseCase        $find_student,
        private UpdateStudentDeatilsUseCase     $update_student,
        private AttachStudentDetailUserCase     $attach,
        private BulkImportStudentDetailsUseCase  $importStudentDetail,
        private CacheService                     $service


    ){
        $this->setCacheService($service);
    }

    public function attachStudentDetail(CreateStudentDetailDTO $create): User
    {
        $student=$this->attach->execute($create);
        $this->service->flushTags(array_merge(self::TAG_USERS_ID, ["userId:$student->id"]));
        $this->service->flushTags(array_merge(self::TAG_STUDENT_DETAILS,["userId:$student->id"]));
        return $student;
    }

    public function findStudentDetail(int $user_id): StudentDetail
    {
        return $this->find_student->execute($user_id);
    }

    public function updateStudentDetail(int $user_id, array $fields): User
    {
        $sd= $this->update_student->execute($user_id,$fields);
        $this->service->flushTags(array_merge(self::TAG_USERS_ID, ["userId:$user_id"]));
        $this->service->flushTags(array_merge(self::TAG_STUDENT_DETAILS,["userId:$user_id"]));
        return $sd;
    }

    public function importStudents(array $rows): ImportResponse
    {
        $import=$this->importStudentDetail->execute($rows);
        $this->service->flushTags(self::TAG_USERS_ID);
        return $import;
    }

}
