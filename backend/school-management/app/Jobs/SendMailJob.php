<?php

namespace App\Jobs;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 5;
    public $backoff = [10, 30, 60];

    protected Mailable $mailable;
    protected string $recipientEmail;
    protected ?string $jobType = null;

    /**
     * Create a new job instance.
     */
    public function __construct(Mailable $mailable, string $recipientEmail,  ?string $jobType = null)
    {
        $this->mailable = $mailable;
        $this->recipientEmail = $recipientEmail;
        $this->jobType = $jobType;
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
            $logContext = [
                'email' => $this->recipientEmail,
                'job_type' => $this->jobType,
                'mailable' => get_class($this->mailable)
            ];

            Log::info("Correo enviado exitosamente", $logContext);
        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    private function handleError(\Throwable $e): void
    {
        $message = $e->getMessage();
        $logContext = [
            'email' => $this->recipientEmail,
            'job_type' => $this->jobType,
            'mailable' => get_class($this->mailable),
            'error' => $message,
            'attempt' => $this->attempts()
        ];

        if ($this->isRateLimitError($message)) {
            Log::warning("Rate limit detectado", $logContext);

            $delay = min(300, pow(2, $this->attempts()) * 10); // 10, 40, 90, 160, 300 segundos
            $this->release($delay);
            return;
        }

        Log::error("Error al enviar correo", $logContext);

        if ($this->isTransientError($message)) {
            $this->release(30);
            return;
        }

        if ($this->isPermanentError($message)) {
            Log::error("Error permanente, no se reintentará", $logContext);
            return;
        }

        throw $e;
    }

    private function isRateLimitError(string $message): bool
    {
        return str_contains($message, '429') ||
            str_contains($message, 'Too Many Requests') ||
            str_contains($message, 'rate limit');
    }

    private function isTransientError(string $message): bool
    {
        return str_contains($message, 'Connection') ||
            str_contains($message, 'timeout') ||
            str_contains($message, 'temporarily') ||
            str_contains($message, 'retry');
    }

    private function isPermanentError(string $message): bool
    {
        return str_contains($message, 'Invalid address') ||
            str_contains($message, 'Mailbox not found') ||
            str_contains($message, 'User unknown') ||
            str_contains($message, '550') ||
            str_contains($message, '554');
    }

    public static function forUser(Mailable $mailable, string $recipientEmail, ?string $jobType = null): self
    {
        return new self($mailable, $recipientEmail, $jobType);
    }

    public static function fromBulkRetry(Mailable $mailable, string $recipientEmail): self
    {
        return new self($mailable, $recipientEmail, 'bulk_retry');
    }
}
