<?php

namespace App\Listeners;

use App\Core\Domain\Enum\User\UserStatus;
use App\Events\AdministrationEvent;
use App\Jobs\SendBulkMailJob;
use App\Mail\CriticalAmountAlertMail;
use App\Models\User;

class SendAmoutExceededNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AdministrationEvent $event): void
    {
        $mandatoryRecipientsRoles = config('concepts.amount.notifications.recipient_roles');
        $mandatoryRecipients = User::whereHas('roles', fn ($q) => $q->whereIn('name', $mandatoryRecipientsRoles))
            ->where('status', UserStatus::ACTIVO)
            ->select(['email', 'name' , 'last_name'])
            ->limit(4)
            ->get();
        $threshold = config('concepts.amount.notifications.threshold');
        $exceededBy = bcsub($event->amount, $threshold);
        $mailables=[];
        $recipientEmails=[];
        foreach ($mandatoryRecipients as $recipient) {
            $fullName = $recipient->name . ' ' . $recipient->last_name;
            $mailables[]= new CriticalAmountAlertMail(
                $event->amount,
                $event->id,
                $event->concept_name,
                $fullName,
                $recipient->email,
                $threshold,
                $exceededBy,
                $event->action
            );
            $recipientEmails[] = $recipient->email;
        }
        SendBulkMailJob::forRecipients($mailables, $recipientEmails);

    }
}
