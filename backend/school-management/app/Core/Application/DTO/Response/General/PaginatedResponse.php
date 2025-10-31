<?php

namespace App\Core\Application\DTO\Response\General;

class PaginatedResponse{

     public function __construct(
        public readonly ?array $items,
        public readonly ?int $currentPage,
        public readonly ?int $lastPage,
        public readonly ?int $perPage,
        public readonly ?int $total,
        public readonly ?bool $hasMorePages,
        public readonly ?int $nextPage
    ) {}
}
