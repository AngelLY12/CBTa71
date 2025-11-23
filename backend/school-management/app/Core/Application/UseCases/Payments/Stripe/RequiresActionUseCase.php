<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Jobs\ClearStudentCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\RequiresActionMail;

class RequiresActionUseCase
{
    public function __construct(
        private UserQueryRepInterface $userRepo,
        private PaymentQueryRepInterface $pqRepo,
        private PaymentRepInterface $paymentRepo

    ) {
    }
    public function execute($obj){
        $user = $this->userRepo->getUserByStripeCustomer($obj->customer);
        $payment = $this->pqRepo->findByIntentId($obj->id);
        $data=null;
        $sendMail=false;
        $url=null;
        if (in_array('oxxo', $obj->payment_method_types ?? [])) {
            $url = $obj->next_action->oxxo_display_details->hosted_voucher_url ?? null;
            if($url){
                $data=[
                    'recipientName' => $user->fullName(),
                    'recipientEmail' => $user->email,
                    'concept_name' => $payment->concept_name,
                    'amount'=>$obj->amount,
                    'next_action' => $url,
                    'payment_method_options' => $obj->payment_method_options,
                ];
                $sendMail=true;
            }
        }

        if (in_array('customer_balance', $obj->payment_method_types ?? [])) {
        $url = $obj->next_action->display_bank_transfer_instructions->hosted_instructions_url ?? null;

        if ($url) {
            $data=[
                'recipientName' => $user->fullName(),
                'recipientEmail' => $user->email,
                'concept_name' => $payment->concept_name,
                'amount'=>$obj->amount,
                'next_action' => $url,
                'payment_method_options' => $obj->payment_method_options,
            ];
            $sendMail=true;
            }
        }

        $objStatus = EnumMapper::fromStripe($obj->status);
        $this->paymentRepo->update($payment->id,['status'=>$objStatus->value,'url'=>$url ?? $payment->url]);
        if($sendMail && $data){
            $mail = new RequiresActionMail(MailMapper::toRequiresActionEmailDTO($data));
            SendMailJob::dispatch($mail, $user->email)->delay(now()->addSeconds(rand(1, 5)));
            ClearStudentCacheJob::dispatch($user->id);
            return true;
        }

        return false;
    }

}
