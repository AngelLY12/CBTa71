<?php

namespace App\Core\Application\Traits;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
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
    private function handlePaymentSession($session, array $fields)
    {
        $payment = $this->pqRepo->findBySessionId($session->id);
        if(!$payment){
            logger()->warning("No se encontró el pago con session_id={$session->id}");
            throw new ModelNotFoundException("No se encontró el pago con session_id={$session->id}");
        }
        $user = $this->userRepo->getUserByStripeCustomer($session->customer);

        $payment=$this->paymentRepo->update($payment->id,$fields);
        if($payment->status==='paid'){
           $data = MailMapper::toPaymentCreatedEmailDTO($payment, $user->fullName(), $user->email);
           $mail = new PaymentCreatedMail($data);
           SendMailJob::dispatch($mail, $user->email)->delay(now()->addSeconds(rand(1, 5)));
        }

        return $payment;
    }


}
