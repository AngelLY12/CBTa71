<?php

namespace App\Jobs;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 5;
    public $backoff = [10, 30, 60];

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
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->recipientEmail)->send($this->mailable);
            Log::info("Correo enviado exitosamente a {$this->recipientEmail}");
        } catch (\Throwable $e) {
           $message = $e->getMessage();

            if (str_contains($message, '429') || str_contains($message, 'Too Many Requests')) {
                Log::warning("Rate limit detectado al enviar correo a {$this->recipientEmail}, reintentando en 10s...");
                $this->release(10);
                return;
            }

            Log::error("Error al enviar correo a {$this->recipientEmail}: {$message}");
            throw $e;
        }
    }
}
