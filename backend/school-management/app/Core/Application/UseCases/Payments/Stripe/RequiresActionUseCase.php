<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Jobs\SendMailJob;
use App\Mail\RequiresActionMail;
use Stripe\Stripe;

class RequiresActionUseCase
{
    public function __construct(
        private UserRepInterface $userRepo,
        private PaymentQueryRepInterface $pqRepo

    ) {
        Stripe::setApiKey(config('services.stripe.secret'));

    }
    public function execute($obj){
        $user = $this->userRepo->getUserByStripeCustomer($obj->customer);
        $concept_name = $this->pqRepo->getConceptNameFromPayment($obj->id);
        $data=null;
        $sendMail=false;
        if (in_array('oxxo', $obj->payment_method_types ?? [])) {
            $data=[
                'recipientName' => $user->fullName(),
                'recipientEmail' => $user->email,
                'concept_name' => $concept_name,
                'amount'=>$obj->amount,
                'next_action' => $obj->next_action,
                'payment_method_options' => $obj->payment_method_options,
            ];
            $sendMail=true;
        }

        if (in_array('customer_balance', $obj->payment_method_types ?? [])) {
        $bankTransfer = $obj->next_action->display_bank_transfer_instructions ?? null;

        if ($bankTransfer) {
            $data=[
                'recipientName' => $user->fullName(),
                'recipientEmail' => $user->email,
                'concept_name' => $concept_name,
                'amount'=>$obj->amount,
                'next_action' => $obj->next_action,
                'payment_method_options' => $obj->payment_method_options,
            ];
            $sendMail=true;
            }
        }
        if($sendMail && $data){
            $mail = new RequiresActionMail(MailMapper::toRequiresActionEmailDTO($data));
            SendMailJob::dispatch($mail, $user->email);
            return true;
        }

        return false;
    }

}
