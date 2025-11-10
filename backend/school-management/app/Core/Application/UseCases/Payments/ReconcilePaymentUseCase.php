<?php

namespace App\Core\Application\UseCases\Payments;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Infraestructure\Cache\CacheService;
use App\Exceptions\DomainException;
use App\Exceptions\NotFound\PaymentMethodNotFoundException;
use App\Exceptions\ServerError\PaymentNotificationException;
use App\Exceptions\ServerError\PaymentReconciliationException;
use App\Jobs\ClearStaffCacheJob;
use App\Jobs\ClearStudentCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\PaymentValidatedMail;

class ReconcilePaymentUseCase
{
    public function __construct(
        private PaymentQueryRepInterface $pqRepo,
        private StripeGatewayInterface $stripe,
        private UserQueryRepInterface $userRepo,
        private PaymentMethodQueryRepInterface $pmRepo,
        private CacheService $cacheService
    )
    {}
    public function execute():void
    {
        $affectedUsers = [];
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
                $affectedUsers[] = $payment->user_id;

            } catch (DomainException $e) {
                logger()->warning("[ReconcilePayment] {$e->getMessage()} (code: {$e->getCode()})");
            } catch (\Throwable $e) {
                logger()->error("Error al reconciliar el pago {$payment->id}: {$e->getMessage()}");
            }
        }
        ClearStaffCacheJob::dispatch()->delay(now()->addSeconds(rand(1, 10)));
        foreach (array_unique($affectedUsers) as $userId) {
            ClearStudentCacheJob::dispatch($userId)->delay(now()->addSeconds(rand(1, 10)));
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
            SendMailJob::dispatch($mail, $user->email)->delay(now()->addSeconds(rand(1, 5)));

        }catch (DomainException $e) {
            logger()->warning("[ReconcilePayment] {$e->getMessage()} (code: {$e->getCode()})");
        } catch (\Throwable $e) {
            logger()->error("Error al reconciliar el pago {$payment->id}: {$e->getMessage()}");
        }
    }
}
