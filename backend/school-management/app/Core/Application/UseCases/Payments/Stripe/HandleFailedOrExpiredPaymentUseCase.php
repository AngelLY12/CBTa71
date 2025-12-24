<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\DomainException;
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
        try {


            if (in_array($eventType, ['payment_intent.payment_failed', 'payment_intent.canceled'])) {
                $payment = $this->pqRepo->findByIntentId($obj->id);
                $error = $obj->last_payment_error->message ?? 'Error desconocido';
            } elseif ($eventType === 'checkout.session.expired') {
                $payment = $this->pqRepo->findBySessionId($obj->id);
                $error = "La sesión de pago expiró";
            }

            $user = $this->uqRepo->getUserByStripeCustomer($obj->customer);
            if (!$user) {
                logger()->error("Usuario no encontrado para customer: {$obj->customer}");
                return false;
            }

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
                SendMailJob::forUser($mail, $user->email, 'failed_or_expired_payment')->onQueue('emails');
                $this->paymentRepo->delete($payment->id);
                ClearStudentCacheJob::dispatch($user->id)->onQueue('cache');
                return true;
            }
            return false;
        }catch (DomainException $e) {
                logger()->warning("Excepción de dominio en webhook: " . $e->getMessage(), [
                    'exception' => get_class($e),
                    'use_case' => static::class
                ]);
                return false;
        } catch (\Illuminate\Validation\ValidationException $e) {
            logger()->warning("Excepción de validación en webhook: " . $e->getMessage());
            return false;

        } catch (\Exception $e) {
            logger()->error("Error inesperado en webhook: " . $e->getMessage(), [
                'exception' => get_class($e),
                'use_case' => static::class,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

}
