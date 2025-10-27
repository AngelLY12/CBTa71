<?php

namespace App\Jobs;

use App\Core\Application\Services\Payments\ReconcilePaymentsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class ReconcilePayments implements ShouldQueue
{
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];


    /**
     * Create a new job instance.
     */

    /**
     * Execute the job.
     */
    public function handle(ReconcilePaymentsService $service): void
    {
        $service->reconcile();
    }
}
