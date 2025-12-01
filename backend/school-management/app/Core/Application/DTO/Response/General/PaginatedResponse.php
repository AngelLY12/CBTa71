<?php

namespace App\Core\Application\DTO\Response\General;


/**
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     type="object",
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(type="object"),
 *         nullable=true,
 *         description="Lista de elementos paginados"
 *     ),
 *     @OA\Property(property="currentPage", type="integer", example=1),
 *     @OA\Property(property="lastPage", type="integer", example=5),
 *     @OA\Property(property="perPage", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=72),
 *     @OA\Property(property="hasMorePages", type="boolean", example=true),
 *     @OA\Property(property="nextPage", type="integer", example=2)
 * )
 */

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
