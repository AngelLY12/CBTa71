<?php

namespace App\Jobs;

use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckUserStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user=$user;
    }

    /**
     * Execute the job.
     */
    public function handle(CacheService $service): void
    {

        if (method_exists($this->user, 'tokens')) {
            $this->user->tokens()->delete();
            $this->user->refreshTokens()->delete();

        }
        $service->clearKey(CachePrefix::STUDENT->value, StudentCacheSufix::CARDS->value . ":show:$this->user->id");
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("Job falló verificando estatus del usuario", [
            'error' => $exception->getMessage()
        ]);
    }
}
