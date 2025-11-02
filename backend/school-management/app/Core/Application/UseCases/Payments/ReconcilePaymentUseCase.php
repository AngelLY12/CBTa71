<?php

namespace App\Core\Application\UseCases\Payments;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Exceptions\DomainException;
use App\Exceptions\NotFound\PaymentMethodNotFoundException;
use App\Exceptions\ServerError\PaymentNotificationException;
use App\Exceptions\ServerError\PaymentReconciliationException;
use App\Jobs\SendMailJob;
use App\Mail\PaymentValidatedMail;

class ReconcilePaymentUseCase
{
    public function __construct(
        private PaymentQueryRepInterface $pqRepo,
        private StripeGatewayInterface $stripe,
        private UserRepInterface $userRepo,
        private PaymentMethodRepInterface $pmRepo
    )
    {}
    public function execute():void
    {
        foreach ($this->pqRepo->getPaidWithinLastMonthCursor() as $payment) {
            try {
                [$pi, $charge] = $this->stripe->getIntentAndCharge($payment->payment_intent_id);

                if (!$pi || !$charge) {
                    throw new PaymentReconciliationException("No se obtuvo información válida desde Stripe para el intent {$payment->payment_intent_id}");
                }
                $pm=$this->pmRepo->findByStripeId($charge->payment_method);
                if (!$pm) {
                    throw new PaymentMethodNotFoundException();
                }
                $this->pqRepo->updatePaymentWithStripeData($payment, $pi, $charge,$pm);
                $this->notifyUser($payment);

            } catch (DomainException $e) {
                logger()->warning("[ReconcilePayment] {$e->getMessage()} (code: {$e->getCode()})");
            } catch (\Throwable $e) {
                logger()->error("Error al reconciliar el pago {$payment->id}: {$e->getMessage()}");
            }
        }

    }

    public function notifyUser(Payment $payment): void
    {
        try{
            $user= $this->userRepo->findById($payment->user_id);
            if (!$user) {
                throw new PaymentNotificationException("Usuario con ID {$payment->user_id} no encontrado.");
            }
            $details = $payment->payment_method_details ?? [];

             $data = [
                'recipientName' => $user->fullName(),
                'recipientEmail' => $user->email,
                'concept_name'       => $payment->concept_name,
                'amount'             => $payment->amount,
                'payment_method_detail' => $details,
                'url'                => $payment->url ?? null,
                'payment_intent_id'  => $payment->payment_intent_id,
            ];

            $mail = new PaymentValidatedMail(MailMapper::toPaymentValidatedEmailDTO($data));
            SendMailJob::dispatch($mail, $user->email);

        }catch (DomainException $e) {
            logger()->warning("[ReconcilePayment] {$e->getMessage()} (code: {$e->getCode()})");
        } catch (\Throwable $e) {
            logger()->error("Error al reconciliar el pago {$payment->id}: {$e->getMessage()}");
        }
    }
}
