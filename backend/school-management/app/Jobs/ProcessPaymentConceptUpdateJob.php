<?php

namespace App\Jobs;

use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptRelationsDTO;
use App\Core\Application\UseCases\Jobs\ProcessUpdateConceptRecipientsUseCase;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentConceptUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $newPaymentConceptId;
    protected ?array $oldPaymentConceptArray;
    protected array $dtoArray;
    protected string $appliesTo;
    protected array $oldRecipientIds;
    /**
     * Create a new job instance.
     */
    public function __construct(int $newPaymentConceptId, array $oldPaymentConceptArray, array $dtoArray, string $appliesTo, array $oldRecipientIds)
    {
        $this->newPaymentConceptId = $newPaymentConceptId;
        $this->oldPaymentConceptArray = $oldPaymentConceptArray;
        $this->dtoArray = $dtoArray;
        $this->appliesTo = $appliesTo;
        $this->oldRecipientIds = $oldRecipientIds;
    }

    /**
     * Execute the job.
     */
    public function handle(ProcessUpdateConceptRecipientsUseCase $update, PaymentConceptQueryRepInterface $pcqRep): void
    {
        $newPaymentConcept = $pcqRep->findById($this->newPaymentConceptId);
        if (!$newPaymentConcept) {
            Log::warning('Payment concept not found for notification job', [
                'concept_id' => $this->newPaymentConceptId
            ]);
            return;
        }
        if(!$this->oldPaymentConceptArray)
        {
            Log::warning('Payment concept not found for notification job', [
                'concept_id' => $this->newPaymentConceptId
            ]);
            return;
        }
        $oldPaymentConcept = PaymentConcept::fromArray($this->oldPaymentConceptArray);
        $dto=UpdatePaymentConceptRelationsDTO::fromArray($this->dtoArray);
        $update->execute($newPaymentConcept, $oldPaymentConcept, $this->oldRecipientIds ,$dto, $this->appliesTo);
        Log::info('Payment concept recipients processed', [
            'concept_id' => $this->newPaymentConceptId,
            'applies_to' => $this->appliesTo
        ]);

    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('ProcessPaymentConceptRecipientsJob failed', [
            'concept_id' => $this->newPaymentConceptId,
            'applies_to' => $this->appliesTo,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    public static function forUpdateConcept(int $newPaymentConceptId, array $oldPaymentConceptArray, array $oldRecipientIds ,array $dtoArray, string $status): self
    {
        return new self($newPaymentConceptId, $oldPaymentConceptArray, $dtoArray, $status, $oldRecipientIds);
    }
}
