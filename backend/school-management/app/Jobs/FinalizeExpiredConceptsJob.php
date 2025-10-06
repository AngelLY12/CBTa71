<?php
namespace App\Jobs;

use App\Models\PaymentConcept;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class FinalizeExpiredConceptsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle()
    {
        $today = Carbon::today();

        $concepts = PaymentConcept::where('status', 'activo')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($concepts as $concept) {
            $concept->status = 'finalizado';
            $concept->save();
        }

        #\Log::info('FinalizeExpiredConceptsJob: '.$concepts->count().' conceptos finalizados.');
    }
}
