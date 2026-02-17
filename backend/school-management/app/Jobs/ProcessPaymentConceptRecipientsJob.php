<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\ProcessPaymentConceptRecipientsUseCase;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class ProcessPaymentConceptRecipientsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $paymentConceptId;
    protected string $appliesTo;

    public function __construct(int $paymentConceptId, string $appliesTo)
    {
        $this->paymentConceptId = $paymentConceptId;
        $this->appliesTo = $appliesTo;
    }

    /**
     * Execute the job.
     */
    public function handle(ProcessPaymentConceptRecipientsUseCase $process, PaymentConceptQueryRepInterface $pcqRep): void
    {
        $paymentConcept = $pcqRep->findById($this->paymentConceptId);
        if (!$paymentConcept) {
            Log::warning('Payment concept not found for notification job', [
                'concept_id' => $this->paymentConceptId
            ]);
            return;
        }

        $process->execute($paymentConcept, $this->appliesTo);
        Log::info('Payment concept recipients processed', [
            'concept_id' => $this->paymentConceptId,
            'applies_to' => $this->appliesTo
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('ProcessPaymentConceptRecipientsJob failed', [
            'concept_id' => $this->paymentConceptId,
            'applies_to' => $this->appliesTo,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    public static function forConcept(int $paymentConceptId, string $status): self
    {
        return new self($paymentConceptId, $status);
    }
}
