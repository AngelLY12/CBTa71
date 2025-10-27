<?php

namespace App\Core\Domain\Entities;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Date;

class PaymentConcept
{
    public function __construct(
        public ?int $id=null,
        public string $concept_name,
        public ?string $description=null,
        public string $status,
        public Carbon $start_date,
        public ?Carbon $end_date=null,
        public int $amount,
        public string $applies_to,
        public bool $is_global,
        private array $userIds = [],
        private array $careerIds = [],
        private array $semesters = []
    ) {}

    public function isActive(): bool
    {
        return $this->status === 'activo';
    }
    public function isDisable(): bool
    {
        return $this->status === 'desactivado';
    }

    public function isFinalize(): bool
    {
        return $this->status === 'finalizado';
    }

    public function isDelete(): bool
    {
        return $this->status === 'eliminado';
    }

     public function isExpired(): bool
    {
        $today = Carbon::today();
        if ($this->end_date && $today > $this->end_date) {
            return true;
        }
        return false;
    }

    public function hasStarted(): bool
    {
        $today = Carbon::today();
        return $today >= $this->start_date;
    }

    public function setUserIds(array $ids): void { $this->userIds = $ids; }
    public function getUserIds(): array { return $this->userIds; }

    public function setCareerIds(array $ids): void { $this->careerIds = $ids; }
    public function getCareerIds(): array { return $this->careerIds; }

    public function setSemesters(array $semesters): void { $this->semesters = $semesters; }
    public function getSemesters(): array { return $this->semesters; }

}
