<?php
namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\FinalizePaymentConceptsUseCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeExpiredConceptsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    public function handle(FinalizePaymentConceptsUseCase $finalize)
    {
        $finalize->execute();
        #\Log::info('FinalizeExpiredConceptsJob: '.$concepts->count().' conceptos finalizados.');
    }
}
