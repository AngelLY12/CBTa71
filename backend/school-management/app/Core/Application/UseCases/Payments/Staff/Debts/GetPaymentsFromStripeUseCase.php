<?php

namespace App\Core\Application\UseCases\Payments\Staff\Debts;

use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Exceptions\ValidationException;

class GetPaymentsFromStripeUseCase
{
     public function __construct(
        public UserQueryRepInterface $uqRepo,
        public StripeGatewayInterface $stripeRepo
    )
    {
    }
    public function execute(string $search, ?int $year=null):array
    {
        if ($year !== null && ($year < 2024 || $year > (int)date('Y'))) {
            throw new ValidationException("El año especificado no es válido.");
        }
        $student=$this->uqRepo->findBySearch($search);
        if (!$student || !$student->stripe_customer_id) {
            return [];
        }
        $sessions = $this->stripeRepo->getStudentPaymentsFromStripe($student,$year);

        return array_map(fn($s) => GeneralMapper::toStripePaymentResponse($s), $sessions);
    }
}
