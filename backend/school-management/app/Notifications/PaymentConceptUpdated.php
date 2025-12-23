<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PaymentConceptUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $paymentConcept;
    protected array $changes;

    public function __construct($paymentConcept, array $changes)
    {
        $this->paymentConcept = $paymentConcept;
        $this->changes = $changes;
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

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage($notifiable),
            'concept_id' => $this->paymentConcept->id,
            'concept_name' => $this->paymentConcept->concept_name,
            'amount' => $this->paymentConcept->amount,
            'start_date' => $this->paymentConcept->start_date?->toISOString(),
            'end_date' => $this->paymentConcept->end_date?->toISOString(),
            'changes' => $this->getFilteredChanges(),
            'action' => $this->determineMainChangeType(),
            'type' => 'payment_concept_changed',
            'priority' => 'high',
            'created_at' => now()->toISOString(),
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
        return $this->toDatabase($notifiable);
    }

    private function getTitle(): string
    {
        $mainChangeType = $this->determineMainChangeType();

        return match($mainChangeType) {
            'relation_update' => 'Actualización del concepto de pago',
            'applies_to_changed' => 'Nuevo concepto de pago aplicado',
            'exceptions_update' => 'Actualización de las excepciones del concepto de pago',
            default => 'Actualización de concepto de pago'
        };
    }

    private function determineMainChangeType(): string
    {
        foreach ($this->changes as $change) {
            if ($change['type'] === 'applies_to_changed') {
                return 'applies_to_changed';
            }
            if ($change['type'] === 'exceptions_update') {
                return 'exceptions_update';
            }
            if ($change['type'] === 'relation_update') {
                return 'relation_update';
            }
        }

        return 'field_update';
    }


    private function getMessage(object $notifiable): string
    {
        $conceptName = $this->paymentConcept->concept_name;
        $amount = number_format($this->paymentConcept->amount, 2);

        if (empty($this->changes)) {
            return "El concepto '{$conceptName}' con monto ({$amount} MXN) ha sido actualizado.";
        }

        $changeMessages = [];
        $userId = $notifiable->id;
        foreach ($this->changes as $change) {

            if ($change['field'] === 'is_global') {
                $globalStatus = $change['new'] ? 'global' : 'no global';
                $changeMessages[] = "El concepto ahora es {$globalStatus}";
            } elseif ($change['type'] === 'applies_to_changed') {
                $changeMessages[] = "El concepto ahora aplica a: {$change['new']}";
            } elseif ($change['type'] === 'relation_update') {
                if($change['field'] === 'semesters')
                {
                    $changeMessages[] = "El ceoncepto ahora aplica al semestre {$change['added']}";
                }
                if($change['field'] === 'applicant_tags')
                {
                    $changeMessages[] = "El concepto de pago ahora aplica al tag {$change['added']}";
                }
                if($change['field'] === 'students')
                {
                    $changeMessages[] = "El concepto de pago ahora aplica para ti}";
                }
                if($change['field'] === 'careers')
                {
                    $changeMessages[] = "El concepto de pago ahora aplica a tu carrera";
                }
            }elseif ($change['type'] === 'exceptions_update')
            {
                if (!empty(array_intersect($change['added'], [$userId]))) {
                    $changeMessages[] = "Fuiste agregado a las excepciones del concepto de pago, ya no aplica a ti";
                }
                if (!empty(array_intersect($change['removed'], [$userId]))) {
                    $changeMessages[] = "Fuiste eliminado de las excepciones del concepto de pago, ahora aplica a ti y debes pagar";
                }
            }

        }

        $baseMessage = "El concepto '{$conceptName}' ({$amount} MXN) ha sido actualizado.";

        if (!empty($changeMessages)) {
            $limitedChanges = array_slice($changeMessages, 0, 3);
            $baseMessage .= " Cambios: " . implode(', ', $limitedChanges);

            if (count($changeMessages) > 3) {
                $baseMessage .= ", y otros cambios más.";
            } else {
                $baseMessage .= ".";
            }
        }

        return $baseMessage;
    }
    private function getFilteredChanges(): array
    {
        $relevantFields = ['relation_update', 'applies_to_changed', 'exceptions_update'];

        return array_filter($this->changes, function($change) use ($relevantFields) {
            return in_array($change['type'], $relevantFields);
        });
    }

}
