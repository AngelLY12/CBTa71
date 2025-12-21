<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Misc\CreateUserActionLogUseCase;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogUserActionJob implements ShouldQueue
{
    use Queueable;

    private User $user;
    private Request $request;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Request $request)
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
