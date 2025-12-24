<?php

namespace App\Core\Application\Traits;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotFound\PaymentNotFountException;
use App\Jobs\ClearStudentCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\PaymentCreatedMail;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasPaymentSession
{
    public function __construct(
        private UserQueryRepInterface $userRepo,
        private PaymentRepInterface $paymentRepo,
        private PaymentQueryRepInterface $pqRepo,
    ) {

    }
    public function handlePaymentSession($session, array $fields)
    {
        $payment = $this->pqRepo->findBySessionId($session->id);
        if(!$payment){
            logger()->warning("No se encontró el pago con session_id={$session->id}");
            throw new PaymentNotFountException();
        }
        $user = $this->userRepo->getUserByStripeCustomer($session->customer);
        $status=$fields['status'];
        $received=$status===PaymentStatus::PAID ? bcdiv((string)($session->amount_received ?? 0), '100', 2) : null;
        $fields['amount_received']=$received;
        $payment=$this->paymentRepo->update($payment->id,$fields);
        if($payment->status===PaymentStatus::PAID){
           $data = MailMapper::toPaymentCreatedEmailDTO($payment, $user->fullName(), $user->email);
           $mail = new PaymentCreatedMail($data);
           SendMailJob::forUser($mail, $user->email,'stripe_session')->onQueue('emails');
        }
        ClearStudentCacheJob::dispatch($user->id)->onQueue('cache');
        return $payment;
    }
}
