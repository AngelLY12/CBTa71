<?php

namespace App\Core\Application\UseCases\Payments;

use App\Core\Application\DTO\Response\General\ReconciliationResult;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Application\Traits\HasPaymentStripe;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\Stripe\StripeGatewayQueryInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\DomainException;
use App\Exceptions\NotFound\PaymentMethodNotFoundException;
use App\Exceptions\ServerError\PaymentNotificationException;
use App\Jobs\ClearCacheForUsersJob;
use App\Jobs\ClearStaffCacheJob;
use App\Jobs\SendBulkMailJob;
use App\Jobs\SendMailJob;
use App\Mail\PaymentValidatedMail;

class ReconcilePaymentUseCase
{
    use HasPaymentStripe;
    public function __construct(
        private PaymentQueryRepInterface $pqRepo,
        private StripeGatewayQueryInterface $stripe,
        private UserQueryRepInterface $userRepo,
        private PaymentMethodQueryRepInterface $pmRepo,
        private PaymentRepInterface $paymentRep,
    )
    {
        $this->setRepository($paymentRep);
    }
    public function execute(): ReconciliationResult
    {
        $result = new ReconciliationResult();
        $affectedUsers = [];
        $paymentIntentIds = [];
        $payments = [];
        $paymentsByUserId = [];

        foreach ($this->pqRepo->getPaidWithinLastMonthCursor() as $payment) {
            $paymentIntentIds[] = $payment->payment_intent_id;
            $payments[] = $payment;
        }

        if (empty($paymentIntentIds)) {
            return $result;
        }

        $stripeData = $this->stripe->getIntentsAndChargesBatch($paymentIntentIds);

        $paymentMethodIds = [];
        foreach ($stripeData as [$intent, $charge]) {
            if ($charge && $charge->payment_method) {
                $paymentMethodIds[] = $charge->payment_method;
            }
        }

        $paymentMethods = $this->pmRepo->findByStripeIds(array_unique($paymentMethodIds));
        $paymentMethodMap = [];
        foreach ($paymentMethods as $pm) {
            $paymentMethodMap[$pm->stripe_payment_method_id] = $pm;
        }

        foreach ($payments as $payment) {
            $result->processed++;

            try {
                if (!isset($stripeData[$payment->payment_intent_id])) {
                    continue;
                }

                [$pi, $charge] = $stripeData[$payment->payment_intent_id];

                $pm = $paymentMethodMap[$charge->payment_method] ?? null;

                if (!$pm) {
                    $pm = $this->pmRepo->findByStripeId($charge->payment_method);

                    if (!$pm) {
                        throw new PaymentMethodNotFoundException();
                    }
                }

                $newPayment = $this->updatePaymentWithStripeData($payment, $pi, $charge, $pm);
                $result->updated++;

                $paymentsByUserId[$newPayment->user_id][] = $newPayment;
                $affectedUsers[] = $newPayment->user_id;

            } catch (DomainException $e) {
                $result->failed++;
                logger()->warning("[ReconcilePayment] {$e->getMessage()}");
            } catch (\Throwable $e) {
                $result->failed++;
                logger()->error("Error en pago {$payment->id}: {$e->getMessage()}");
            }
        }

        if ($result->updated > 0) {
            $this->clearCaches($affectedUsers);
        }
        $this->notifyUsersBatch($paymentsByUserId);

        return $result;

    }

    private function notifyUsersBatch(array $paymentsByUserId): void
    {
        if (empty($paymentsByUserId)) {
            return;
        }

        $userIds = array_keys($paymentsByUserId);
        $users = $this->userRepo->findByIds($userIds);

        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->id] = $user;
        }
        $mailables = [];
        $recipientEmails = [];

        foreach ($paymentsByUserId as $userId => $userPayments) {
            $user = $userMap[$userId] ?? null;
            if (!$user) {
                continue;
            }

            foreach ($userPayments as $payment) {
                $data = [
                    'recipientName' => $user->fullName(),
                    'recipientEmail' => $user->email,
                    'concept_name' => $payment->concept_name,
                    'amount' => $payment->amount,
                    'amount_received' => $payment->amount_received,
                    'payment_method_detail' => $payment->payment_method_details ?? [],
                    'status' => $payment->status->value,
                    'url' => $payment->url ?? null,
                    'payment_intent_id' => $payment->payment_intent_id,
                ];

                $mailables[] = new PaymentValidatedMail(
                    MailMapper::toPaymentValidatedEmailDTO($data)
                );
                $recipientEmails[] = $user->email;
            }
        }

        if (!empty($mailables)) {
            $this->sendBulkEmails($mailables, $recipientEmails);
        }
    }

    private function sendBulkEmails(array $mailables, array $recipientEmails): void
    {
        $chunkSize = 50;
        $total = count($mailables);

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $mailablesChunk = array_slice($mailables, $i, $chunkSize);
            $emailsChunk = array_slice($recipientEmails, $i, $chunkSize);

            SendBulkMailJob::forRecipients(
                $mailablesChunk,
                $emailsChunk,
                'bulk_reconcile_payment'
            )
                ->onQueue('emails')
                ->delay(now()->addSeconds(5));
        }
    }

    private function clearCaches(array $affectedUsers): void
    {
        $uniqueUserIds = array_unique($affectedUsers);

        foreach (array_chunk($uniqueUserIds, 100) as $chunk) {
            ClearCacheForUsersJob::forStudents($chunk)
                ->onQueue('cache')
                ->delay(now()->addSeconds(5));
        }

        ClearStaffCacheJob::dispatch()
            ->onQueue('cache')
            ->delay(now()->addSeconds(5));
    }

}
