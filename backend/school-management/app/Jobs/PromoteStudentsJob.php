<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\PromoteStudentsUseCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PromoteStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PromoteStudentsUseCase $promote): void
    {
        try {
            $data = $promote->execute();
            Log::info('Estudiantes promovidos: ' . json_encode($data));
        } catch (\Throwable $e) {
            Log::warning('Promoción no ejecutada automáticamente: ' . $e->getMessage());
        }
    }
}
