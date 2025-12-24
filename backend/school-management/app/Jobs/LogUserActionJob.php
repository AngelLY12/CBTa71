<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Misc\CreateUserActionLogUseCase;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogUserActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;
    private array $request;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, array $request)
    {
        $this->user=$user;
        $this->request=$request;

    }

    /**
     * Execute the job.
     */
    public function handle(CreateUserActionLogUseCase $create): void
    {
        try{
            $log=$create->execute($this->user, $this->request);
            Log::info('UserActionLog creado', $log->toArray());
        }catch (\Throwable $e) {
            Log::warning('No se pudo crear el log: ' . $e->getMessage());
        }

    }
    public function failed(\Throwable $exception): void
    {
        Log::critical("Job falló creando log de acción", [
            'error' => $exception->getMessage()
        ]);
    }
}
