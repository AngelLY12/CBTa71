<?php

namespace App\Core\Application\UseCases\Payments\Staff\Debts;

use App\Core\Application\DTO\Response\Payment\PaymentValidateResponse;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Application\Services\Payments\Staff\PaymentValidationService;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\User;
use Illuminate\Support\Facades\DB;
use App\Core\Application\Mappers\UserMapper as AppUserMapper;
use App\Jobs\ClearStaffCacheJob;
use App\Jobs\ClearStudentCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\PaymentValidatedMail;

class ValidatePaymentUseCase{

    private const CACHE_DELAY_MIN = 1;
    private const CACHE_DELAY_MAX = 10;
    private const EMAIL_DELAY_MIN = 1;
    private const EMAIL_DELAY_MAX = 5;

    public function __construct(
        private PaymentValidationService $validationService
    )
    {
    }
    public function execute(string $search, string $payment_intent_id): PaymentValidateResponse
    {
        [$payment, $student, $wasValidated] = DB::transaction(
            fn() => $this->validationService->validateAndGetOrCreatePayment($search, $payment_intent_id)
        );

        if ($wasValidated) {
            $this->processSideEffects($payment, $student);
        }

        return $this->buildResponse($payment, $student);

    }

    private function processSideEffects(Payment $payment, User $student): void
    {
        $this->dispatchCacheClearing($payment->user_id);

        $this->sendValidationEmail($payment, $student);

    }

    private function dispatchCacheClearing(int $userId): void
    {
        ClearStudentCacheJob::dispatch($userId)
            ->delay(now()->addSeconds(
                rand(self::CACHE_DELAY_MIN, self::CACHE_DELAY_MAX)
            ));

        ClearStaffCacheJob::dispatch()
            ->delay(now()->addSeconds(
                rand(self::CACHE_DELAY_MIN, self::CACHE_DELAY_MAX)
            ));
    }

    private function sendValidationEmail(Payment $payment, User $student): void
    {
        $data = [
            'recipientName' => $student->fullName(),
            'recipientEmail' => $student->email,
            'concept_name' => $payment->concept_name,
            'amount' => $payment->amount,
            'payment_method_detail' => $payment->payment_method_details,
            'url' => $payment->url,
            'payment_intent_id' => $payment->payment_intent_id,
        ];

        $mail = new PaymentValidatedMail(
            MailMapper::toPaymentValidatedEmailDTO($data)
        );

        SendMailJob::forUser($mail, $student->email, 'validate_payment')
            ->delay(now()->addSeconds(
                rand(self::EMAIL_DELAY_MIN, self::EMAIL_DELAY_MAX)
            ));
    }

    private function buildResponse(Payment $payment, User $student): PaymentValidateResponse
    {
        return PaymentMapper::toPaymentValidateResponse(
            AppUserMapper::toDataResponse($student),
            PaymentMapper::toPaymentDataResponse($payment)
        );
    }
}
