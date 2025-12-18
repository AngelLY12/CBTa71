<?php

namespace App\Core\Application\UseCases\Payments\Staff\Debts;

use App\Core\Application\DTO\Response\Payment\PaymentValidateResponse;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\Stripe\StripeGatewayQueryInterface;
use Illuminate\Support\Facades\DB;
use App\Core\Application\Mappers\UserMapper as AppUserMapper;
use App\Core\Application\Traits\HasPaymentStripe;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotFound\ConceptNotFoundException;
use App\Exceptions\NotFound\PaymentMethodNotFoundException;
use App\Exceptions\NotFound\UserNotFoundException;
use App\Jobs\ClearStaffCacheJob;
use App\Jobs\ClearStudentCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\PaymentValidatedMail;

class ValidatePaymentUseCase{

    use HasPaymentStripe;

    public function __construct(
        public UserQueryRepInterface $uqRepo,
        public PaymentRepInterface $paymentRepo,
        public PaymentQueryRepInterface $pqRepo,
        public StripeGatewayQueryInterface $stripeRepo,
        public PaymentMethodQueryRepInterface $pmqRepo,
        public PaymentConceptQueryRepInterface $pcqRepo,
    )
    {
        $this->setRepository($paymentRepo);

    }
    public function execute(string $search, string $payment_intent_id): PaymentValidateResponse
    {
        return DB::transaction(function () use ($search, $payment_intent_id) {
            $student=$this->uqRepo->findBySearch($search);

            if (!$student) {
                throw new UserNotFoundException();
            }

            $payment=$this->pqRepo->findByIntentOrSession($student->id,$payment_intent_id);
            $validated=false;
            if (!$payment) {
                    $stripe=$this->stripeRepo->getIntentAndCharge($payment_intent_id);
                    $paymentConceptId = $stripe['intent']->metadata->payment_concept_id ?? null;
                    $pc= $this->pcqRepo->findById($paymentConceptId) ?? null;
                    $pm = $this->pmqRepo->findByStripeId($stripe['charge']->payment_method);
                    $paymentMethodDetails=null;
                    if (!$pc) throw new ConceptNotFoundException();
                    if (!$pm) throw new PaymentMethodNotFoundException();


                    $paymentMethodDetails = $this->formatPaymentMethodDetails($stripe['charge']->payment_method_details);

                    $payment= new Payment(
                        user_id: $student->id,
                        payment_concept_id:$paymentConceptId,
                        payment_method_id:$pm?->id,
                        stripe_payment_method_id: $stripe['charge']->payment_method ?? null,
                        concept_name: $pc->concept_name,
                        amount: $pc->amount,
                        amount_received: $stripe['charge']->amount_received ?? null,
                        payment_method_details: $paymentMethodDetails,
                        status:$stripe['intent']->status,
                        payment_intent_id:$payment_intent_id,
                        url:$stripe['charge']->receipt_url ?? null,
                        stripe_session_id: $stripe['intent']->latest_charge->id ?? null,
                        created_at: null
                    );
                    $payment = $this->paymentRepo->create($payment);
                    $validated=true;
            }
            else {
                if ($payment->status === PaymentStatus::PAID && empty($payment->payment_method_details)) {
                    logger()->info("Reconciling existing payment ID={$payment->id}");
                    $stripe=$this->stripeRepo->getIntentAndCharge($payment_intent_id);
                    $pm = $this->pmqRepo->findByStripeId($stripe['charge']->payment_method);
                    if (!$pm) throw new PaymentMethodNotFoundException();
                    $this->updatePaymentWithStripeData($payment, $stripe['intent'], $stripe['charge'], $pm);
                    $validated=true;
                }
            }
            $data = [
                'recipientName' => $student->fullName(),
                'recipientEmail' => $student->email,
                'concept_name'       => $payment->concept_name,
                'amount'             => $payment->amount,
                'payment_method_detail' => $payment->payment_method_details,
                'url'                => $payment->url ?? null,
                'payment_intent_id'  => $payment->payment_intent_id,
            ];
            if($validated)
            {
                ClearStudentCacheJob::dispatch($payment->user_id)->delay(now()->addSeconds(rand(1, 10)));
                ClearStaffCacheJob::dispatch()->delay(now()->addSeconds(rand(1, 10)));
            }
            $mail = new PaymentValidatedMail(MailMapper::toPaymentValidatedEmailDTO($data));
            SendMailJob::dispatch($mail, $student->email)->delay(now()->addSeconds(rand(1, 5)));
            return PaymentMapper::toPaymentValidateResponse(AppUserMapper::toDataResponse($student),PaymentMapper::toPaymentDataResponse($payment));
        });

    }
}
