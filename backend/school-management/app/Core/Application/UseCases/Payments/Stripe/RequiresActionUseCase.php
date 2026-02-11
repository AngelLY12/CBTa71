<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentEvent;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Payment\PaymentEventType;
use App\Core\Domain\Repositories\Command\Payments\PaymentEventRepInterface;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentEventQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Helpers\Money;
use App\Exceptions\DomainException;
use App\Exceptions\NotFound\PaymentNotFountException;
use App\Jobs\ClearStudentCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\RequiresActionMail;

class RequiresActionUseCase
{
    public function __construct(
        private UserQueryRepInterface $userRepo,
        private PaymentQueryRepInterface $pqRepo,
        private PaymentRepInterface $paymentRepo,
        private PaymentEventRepInterface $paymentEventRep,
        private PaymentEventQueryRepInterface $paymentEventQueryRep,

    ) {
    }
    public function execute($obj, string $eventId){
        $user = $this->userRepo->getUserByStripeCustomer($obj->customer);
        $payment = $this->pqRepo->findByIntentId($obj->id);
        if (!$payment) {
            logger()->warning("No se encontró el pago con session_id={$obj->id}");
            throw new PaymentNotFountException();
        }

        $event = $this->createPaymentEvent($obj, $payment, $eventId);
        if ($event->processed) {
            logger()->info("PaymentEvent ya procesado: {$event->id} para intent {$obj->id}");
            return true;
        }
        try {
            $data = null;
            $sendMail = false;
            $nextAction = null;
            $url = null;
            if (in_array('oxxo', $obj->payment_method_types ?? [])) {
                $nextAction = $obj->next_action ?? null;
                if ($nextAction) {
                    $url = $nextAction->oxxo_display_details->hosted_voucher_url ?? null;
                    $data = $this->prepareDataForEmail($user, $payment, $obj, $nextAction);
                    $sendMail = true;
                }
            }

            if (in_array('customer_balance', $obj->payment_method_types ?? [])) {
                $nextAction = $obj->next_action ?? null;

                if ($nextAction) {
                    $url = $nextAction->display_bank_transfer_instructions->hosted_instructions_url ?? null;
                    $data = $this->prepareDataForEmail($user, $payment, $obj, $nextAction);
                    $sendMail = true;
                }
            }

            $objStatus = EnumMapper::fromStripe($obj->status);
            $this->paymentRepo->update($payment->id, ['status' => $objStatus->value, 'url' => $url ?? $payment->url]);
            ClearStudentCacheJob::dispatch($user->id)->onQueue('cache');
            if ($sendMail && $data) {
                $this->sendRequiresActionMail($data, $user, $payment, $eventId);
            }

            $this->paymentEventRep->update($event->id, [
                'processed' => true,
                'processed_at' => now(),
                'status' => $objStatus->value,
                'amount_received' => $payment->amount_received,
                'metadata' => array_merge($event->metadata ?? [], [
                    'email_sent' => true,
                    'user_id' => $user->id
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            $this->paymentEventRep->update($event->id, [
                'error_message' => $e->getMessage(),
                'retry_count' => $event->retryCount + 1,
            ]);

            if (!($e instanceof DomainException) && !($e instanceof \Illuminate\Validation\ValidationException)) {
                throw $e;
            }

            logger()->warning("Error procesando evento requires_action: " . $e->getMessage(), [
                'exception' => get_class($e),
                'event_id' => $eventId,
                'payment_intent_id' => $obj->id
            ]);

            return false;
        }
    }

    private function prepareDataForEmail(User $user, Payment $payment, $obj, $nextAction): array
    {
        return
            [
                'recipientName' => $user->fullName(),
                'recipientEmail' => $user->email,
                'concept_name' => $payment->concept_name,
                'amount' => $obj->amount,
                'next_action' => $nextAction,
                'payment_method_options' => $obj->payment_method_options,
            ];

    }

    private function sendRequiresActionMail(array $data, User $user, Payment $payment, string $eventId): void
    {
        $emailEvent = $this->createEmailEvent($data, $user, $payment, $eventId);
        $mail = new RequiresActionMail(MailMapper::toRequiresActionEmailDTO($data));
        SendMailJob::forUser($mail, $user->email, 'requires_action', $emailEvent->id)->onQueue('emails');
    }

    private function createEmailEvent(array $data, User $user, Payment $payment, string $eventId): PaymentEvent
    {
        $emailEvent = PaymentEvent::createEmailEvent(
            paymentId: $payment->id,
            eventId: $eventId,
            paymentIntentId: $payment->payment_intent_id ?? null,
            sessionId: $payment->stripe_session_id ?? null,
            eventType: PaymentEventType::EMAIL_REQUIRES_ACTION,
            recipientEmail: $user->email,
            emailData: [
                'email_template' => 'requires_action',
                'next_action_type' => $data['next_action_type'] ?? 'unknown',
                'concept_name' => $payment->concept_name,
            ]
        );

        return $this->paymentEventRep->create($emailEvent);
    }

    private function createPaymentEvent($obj, Payment $payment, string $eventId): PaymentEvent
    {
        $eventType = PaymentEventType::WEBHOOK_PAYMENT_REQUIRES_ACTION;
        $existing = $this->paymentEventQueryRep->findByStripeEvent($eventId, $eventType);
        if ($existing) {
            return $existing;
        }

        $amount = '0.00';
        if (isset($obj->amount) && $obj->amount > 0) {
            $amount = Money::from($obj->amount)->divide('100')->finalize();
        }

        $event = PaymentEvent::createWebhookEvent(
            paymentId: $payment->id,
            stripeEventId: $eventId,
            paymentIntentId: $obj->id,
            sessionId: $obj->latest_charge ?? null,
            amount: $amount,
            eventType: $eventType,
            metadata: [
                'raw_object' => $obj,
                'stripe_event_type' => 'payment_intent.requires_action',
                'next_action_type' => $this->getNextActionType($obj),
                'email_type' => 'requires_action_mail'

            ],
        );
        return $this->paymentEventRep->create($event);
    }

    private function getNextActionType($obj): string
    {
        if (in_array('oxxo', $obj->payment_method_types ?? [])) {
            return 'oxxo';
        }

        if (in_array('customer_balance', $obj->payment_method_types ?? [])) {
            return 'bank_transfer';
        }

        return 'unknown';
    }

}
