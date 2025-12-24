<?php

namespace App\Jobs;

use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Notifications\PaymentConceptUpdatedFields;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendConceptUpdatedFieldsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $conceptId;
    protected array $changes;

    /**
     * Create a new job instance.
     */
    public function __construct(int $conceptId, array $changes)
    {
        $this->conceptId = $conceptId;
        $this->changes = $changes;
    }

    /**
     * Execute the job.
     */
    public function handle(UserQueryRepInterface $uqRepo, PaymentConceptQueryRepInterface $pcqRepo): void
    {

        $concept=$pcqRepo->findById($this->conceptId);
        $userIds=$uqRepo->getRecipientsIds($concept, $concept->applies_to->value);

        if (empty($userIds)) {
            Log::info('No user IDs to notify', ['concept_id' => $this->conceptId]);
            return;
        }

        $notification = new PaymentConceptUpdatedFields($concept->toArray(), $this->changes);


        foreach (array_chunk($this->userIds, 500) as $chunk) {
            \App\Models\User::whereIn('id', $chunk)
                ->each(function ($user) use ($notification) {
                    $user->notify($notification);
                });

            usleep(100000);
        }

        Log::info('Broadcast notifications job completed', [
            'concept_id' => $this->conceptId,
            'changes_count' => count($this->changes),
            'queue' => $this->queue
        ]);

    }
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send broadcast notifications', [
            'concept_id' => $this->conceptId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    public static function forStudents(int $conceptId, array $changes): self
    {
        return new self($conceptId, $changes);
    }
}
