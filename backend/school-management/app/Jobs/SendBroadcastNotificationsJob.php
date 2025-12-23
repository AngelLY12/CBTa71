<?php

namespace App\Jobs;

use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Notifications\PaymentConceptUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendBroadcastNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $userIds;
    protected int $conceptId;
    protected array $changes;
    /**
     * Create a new job instance.
     */
    public function __construct(array $userIds,
                                int $conceptId,
                                array $changes,
                                )
    {
        $this->userIds = $userIds;
        $this->conceptId = $conceptId;
        $this->changes = $changes;
    }

    /**
     * Execute the job.
     */
    public function handle(UserQueryRepInterface $uqRepo, PaymentConceptQueryRepInterface $pcqRepo): void
    {
        if (empty($this->userIds)) {
            Log::info('No user IDs to notify', ['concept_id' => $this->conceptId]);
            return;
        }

        $users=$uqRepo->findByIds($this->userIds);

        if ($users->isEmpty()) {
            Log::warning('No users found for broadcast notifications', [
                'user_ids' => $this->userIds,
                'concept_id' => $this->conceptId
            ]);
            return;
        }

        $concept=$pcqRepo->findById($this->conceptId);
        $chunkSize = 500;
        $totalNotified = 0;

        foreach ($users->chunk($chunkSize) as $chunk) {
            Notification::send($chunk, new PaymentConceptUpdated($concept, $this->changes));
            $totalNotified += $chunk->count();

            if ($chunk->count() === $chunkSize) {
                usleep(100000);
            }
        }

        Log::info('Broadcast notifications job completed', [
            'concept_id' => $this->conceptId,
            'user_count' => $totalNotified,
            'changes_count' => count($this->changes),
            'queue' => $this->queue
        ]);

    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send broadcast notifications', [
            'concept_id' => $this->conceptId,
            'user_ids_count' => count($this->userIds),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    public static function forStudents(array $userIds, int $conceptId, array $changes): self
    {
        return new self($userIds, $conceptId, $changes);
    }
}
