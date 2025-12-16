<?php

namespace App\Core\Application\Mappers;

use App\Core\Application\DTO\Request\General\LoginDTO;
use App\Core\Application\DTO\Response\General\DashboardDataResponse;
use App\Core\Application\DTO\Response\General\DashboardDataUserResponse;
use App\Core\Application\DTO\Response\General\LoginResponse;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\General\PermissionsByUsers;
use App\Core\Application\DTO\Response\General\StripePaymentsResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Stripe\Checkout\Session;

class GeneralMapper{
    public static function toPaginatedResponse(array $items, LengthAwarePaginator $paginated){
        return new PaginatedResponse(
            items: $items ?? [],
            currentPage: $paginated->currentPage() ?? null,
            lastPage: $paginated->lastPage() ?? null,
            perPage: $paginated->perPage() ?? null,
            total: $paginated->total() ?? null,
            hasMorePages: $paginated->hasMorePages(),
            nextPage: $paginated->currentPage() < $paginated->lastPage() ? $paginated->currentPage() + 1 : null
        );
    }

    public static function toLoginDTO(array $data):LoginDTO
    {
        return new LoginDTO(
            email:$data['email'],
            password:$data['password']
        );
    }

    public static function toLoginResponse(string $token, string $refresh,$token_type, array $data):LoginResponse
    {
        return new LoginResponse(
            access_token:$token ?? null,
            refresh_token: $refresh ?? null,
            token_type:$token_type ?? null,
            user_data:$data ?? []
        );
    }

    public static function toStripePaymentResponse(Session $session): StripePaymentsResponse
    {
            $metadata = $session->metadata ? $session->metadata->toArray() : [];
            return new StripePaymentsResponse(
                id: $session->id ?? null,
                payment_intent_id: $session->payment_intent ?? null,
                concept_name: $metadata['concept_name'] ?? null,
                status: $session->payment_status ?? null,
                amount_total: $session->amount_total ?? null,
                created:$session->created ? date('Y-m-d H:i:s', $session->created) : null
            );
    }

    public static function toDashboardDataResponse(int $ganancias, PendingSummaryResponse $pendientes, int $alumnos):DashboardDataResponse
    {
        return new DashboardDataResponse(
            earnings:$ganancias ?? null,
            pending:$pendientes ?? null,
            students:$alumnos ?? null
        );
    }
    public static function toDashboardDataUserResponse(int $realizados, PendingSummaryResponse $pendientes, PendingSummaryResponse $vencidos):DashboardDataUserResponse
    {
        return new DashboardDataUserResponse(
            completed:$realizados ?? null,
            pending:$pendientes ?? null,
            overdue:$vencidos ?? null
        );
    }

    public static function toPermissionsByUsers(array $data): PermissionsByUsers
    {
        return new PermissionsByUsers(
            role: $data['role'],
            users: $data['users'],
            permissions: $data['permissions']
        );
    }
}
