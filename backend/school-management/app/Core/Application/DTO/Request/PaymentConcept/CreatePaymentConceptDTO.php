<?php

namespace App\Core\Application\DTO\Request\PaymentConcept;

use Carbon\Carbon;

class CreatePaymentConceptDTO {
    public function __construct(
        public string $concept_name,
        public ?string $description,
        public string $amount,
        public string $status,
        public ?Carbon $start_date,
        public ?Carbon $end_date,
        public bool $is_global,
        public string $appliesTo = 'todos',
        public array|int|null $semesters = null,
        public array|int|null $careers = null,
        public array|string|null $students = null,
    ) {}
}
