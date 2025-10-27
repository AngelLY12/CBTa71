<?php

namespace App\Jobs;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Mailable $mailable;
    protected string $recipientEmail;

    /**
     * Create a new job instance.
     */
    public function __construct(Mailable $mailable, string $recipientEmail)
    {
        $this->mailable = $mailable;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->recipientEmail)->send($this->mailable);
        } catch (\Throwable $e) {
            logger()->error('Fallo al enviar correo: ' . $e->getMessage());
        //}catch (ConnectionException $e) {
        //    // reconectar automáticamente
        //    Redis::disconnect();
        //    Redis::connect(config('database.redis.default'));
        //    throw $e;
    }
    }
}
