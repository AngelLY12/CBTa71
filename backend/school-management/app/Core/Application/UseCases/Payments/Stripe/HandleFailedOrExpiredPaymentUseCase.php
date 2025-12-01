<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Jobs\ClearStudentCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\PaymentFailedMail;

class HandleFailedOrExpiredPaymentUseCase
{
    public function __construct(
        private UserQueryRepInterface $uqRepo,
        private PaymentRepInterface $paymentRepo,
        private PaymentQueryRepInterface $pqRepo,
    ) {

    }
    public function execute($obj, string $eventType)
    {
        $payment = null;
        $error = null;

        if (in_array($eventType, ['payment_intent.payment_failed', 'payment_intent.canceled'])) {
            $payment =$this->pqRepo->findByIntentId($obj->id);
            $error = $obj->last_payment_error->message ?? 'Error desconocido';
        } elseif ($eventType === 'checkout.session.expired') {
            $payment = $this->pqRepo->findBySessionId($obj->id);
            $error = "La sesión de pago expiró";
        }

        $user = $this->uqRepo->getUserByStripeCustomer($obj->customer);

        if ($payment && $payment->status !== PaymentStatus::SUCCEEDED->value) {
            logger()->info("Pago fallido eliminado: payment_id={$obj->id}");
            logger()->info("Motivo: {$error}");
            $data = [
                'recipientName' => $user->fullName(),
                'recipientEmail' => $user->email,
                'concept_name' => $payment->concept_name,
                'amount' => $payment->amount,
                'error' => $error
            ];

            $mail = new PaymentFailedMail(MailMapper::toPaymentFailedEmailDTO($data));
            SendMailJob::dispatch($mail, $user->email)->delay(now()->addSeconds(rand(1, 5)));
            $this->paymentRepo->delete($payment->id);
            ClearStudentCacheJob::dispatch($user->id)->delay(now()->addSeconds(rand(1, 10)));;
            return true;
        }
        return false;
    }

}
