<?php
namespace App\Core\Domain\Repositories\Command\Payments;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Domain\Entities\PaymentConcept;


interface PaymentConceptRepInterface {
     //CRUD Methods
    public function findById(int $id): ?PaymentConcept;
    public function create(PaymentConcept $concept): PaymentConcept;
    public function update(PaymentConcept $concept, array $data): PaymentConcept;
    public function deleteLogical(PaymentConcept $concept): PaymentConcept;
    public function delete(PaymentConcept $concept): void;
     //Attach and detach Methods
    public function attachToUsers(PaymentConcept $concept, UserIdListDTO $userIds, bool $replaceRelations=false): PaymentConcept;
    public function attachToCareer(PaymentConcept $concept, array $careerIds, bool $replaceRelations=false): PaymentConcept;
    public function attachToSemester(PaymentConcept $concept, array $semesters, bool $replaceRelations=false): PaymentConcept;
    public function detachFromCareer(PaymentConcept $concept): void;
    public function detachFromSemester(PaymentConcept $concept): void;
    public function detachFromUsers(PaymentConcept $concept): void;
    //Other
    public function finalize(PaymentConcept $concept): PaymentConcept;
    public function disable(PaymentConcept $concept): PaymentConcept;
    public function activate(PaymentConcept $concept): PaymentConcept;
    public function cleanDeletedConcepts():int;
}
