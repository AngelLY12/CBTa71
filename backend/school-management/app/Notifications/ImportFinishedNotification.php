<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFinishedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $importResult
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Import finalizado',
            'message' => "Import de datos finalizado, a continuación veras un resúmen.",
            'details' => $this->buildImportMessage(),
            'type' => 'import_finished'
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => get_class($this),
            'data' => $this->toDatabase($notifiable),
            'read_at' => null,
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }


    private function buildImportMessage(): string
    {
        if (empty($this->importResult['summary'])) {
            return $this->importResult['message']
                ?? 'El import finalizó, pero no se pudo generar el resumen.';
        }

        $summary = $this->importResult['summary'];
        $errors  = $this->importResult['errors'] ?? [];
        $warnings = $this->importResult['warnings'] ?? [];

        $lines = [];

        $lines[] = 'Resumen extendido de la operación:';
        $lines[] = "• Filas recibidas: {$summary['total_rows_received']}";
        $lines[] = "• Filas procesadas: {$summary['rows_processed']}";
        $lines[] = "• Filas insertadas: {$summary['rows_inserted']}";
        $lines[] = "• Filas fallidas: {$summary['rows_failed']}";
        $lines[] = "• Tasa de éxito: {$summary['success_rate']}%";

        if (($warnings['total_warnings'] ?? 0) > 0) {
            $lines[] = "Advertencias: {$warnings['total_warnings']}";
        }

        if (($errors['total_errors'] ?? 0) > 0) {
            $lines[] = "Errores: {$errors['total_errors']}";
        }

        $lines[] = "Fecha: " . ($this->importResult['timestamp'] ?? now()->toDateTimeString());

        return implode("\n", $lines);

    }
}
