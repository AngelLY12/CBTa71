<?php

namespace App\Notifications;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConceptStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private array $concept,
        private string $oldStatus,
        private string $newStatus
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'payment_concept_status_changed',
            'concept_id' => $this->concept['id'],
            'concept_name' => $this->concept['concept_name'],
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'amount' => $this->concept['amount'],
            'applies_to' => $this->concept['applies_to']->value,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'status_transition' => "{$this->oldStatus}_to_{$this->newStatus}",
            'timestamp' => now()->toISOString(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'payment_concept_status_changed',
            'read_at' => null,
            'data' => [
                'title' => 'Estado de concepto actualizado',
                'message' => sprintf(
                    'El concepto "%s" cambió de %s a %s',
                    $this->concept['concept_name'],
                    $this->oldStatus,
                    $this->newStatus
                ),
                'concept_id' => $this->concept['id'],
                'concept_name' => $this->concept['concept_name'],
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'amount' => $this->concept['amount'],
            ],
            'created_at' => now()->toISOString(),
        ]);
    }

    private function getTitle(): string
    {
        switch (true) {
            case $this->oldStatus === PaymentConceptStatus::ACTIVO->value
                && $this->newStatus !== PaymentConceptStatus::ACTIVO->value:
                return match($this->newStatus) {
                    PaymentConceptStatus::FINALIZADO->value => 'Concepto finalizado',
                    PaymentConceptStatus::DESACTIVADO->value => 'Concepto pausado',
                    PaymentConceptStatus::ELIMINADO->value => 'Concepto eliminado',
                    default => 'Estado de concepto actualizado',
                };

            case $this->newStatus === PaymentConceptStatus::ACTIVO->value:
                return match($this->oldStatus) {
                    PaymentConceptStatus::FINALIZADO->value => 'Concepto reactivado',
                    PaymentConceptStatus::DESACTIVADO->value => 'Concepto reactivado',
                    PaymentConceptStatus::ELIMINADO->value => 'Concepto restaurado',
                    default => 'Concepto activado',
                };

            default:
                return 'Estado de concepto actualizado';
        }
    }

    private function getMessage(): string
    {
        $conceptName = $this->concept['concept_name'];

        switch (true) {
            case $this->oldStatus === PaymentConceptStatus::ACTIVO->value
                && $this->newStatus === PaymentConceptStatus::FINALIZADO->value:
                return "El concepto '{$conceptName}' ha sido FINALIZADO. Ya no se aceptan más pagos.";

            case $this->oldStatus === PaymentConceptStatus::ACTIVO->value
                && $this->newStatus === PaymentConceptStatus::DESACTIVADO->value:
                return "El concepto '{$conceptName}' ha sido PAUSADO temporalmente.";

            case $this->oldStatus === PaymentConceptStatus::ACTIVO->value
                && $this->newStatus === PaymentConceptStatus::ELIMINADO->value:
                return "El concepto '{$conceptName}' ha sido ELIMINADO del sistema.";

            case $this->newStatus === PaymentConceptStatus::ACTIVO->value:
                $message = "El concepto '{$conceptName}' está ahora ACTIVO y disponible para pago.";

                if ($this->concept['end_date']) {
                    $message .= " Fecha límite: " . $this->concept['end_date']->format('d/m/Y');
                }

                return $message;

            default:
                return "El concepto '{$conceptName}' cambió de {$this->oldStatus} a {$this->newStatus}.";
        }
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
}
