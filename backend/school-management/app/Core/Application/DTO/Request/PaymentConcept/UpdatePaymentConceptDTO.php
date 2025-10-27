<?php

namespace App\Core\Application\DTO\Request\PaymentConcept;

class UpdatePaymentConceptDTO
{
    public function __construct(
        public int $id,
        public array $fieldsToUpdate,
        public array|int|null $semesters = null,
        public array|int|null $careers = null,
        public array|string|null $students = null,
        public ?string $appliesTo = null,
        public bool $replaceRelations = false
    ) {}
}
