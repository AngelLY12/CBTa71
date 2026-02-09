<?php

namespace App\Core\Domain\Utils\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function expirationToHuman(?Carbon $date): ?string
    {
        if (!$date) {
            return null;
        }

        $now = Carbon::now();

        if ($now->gt($date)) {
            return self::expiredText($date);
        }

        return self::remainingText($date);
    }

    public static function expiredText(Carbon $date): string
    {
        $now = Carbon::now();
        $days = $date->diffInDays($now);

        if ($days == 0) return 'Expirado hoy';
        if ($days == 1) return 'Expirado ayer';
        if ($days < 7) return "Expirado hace {$days} días";

        $weeks = floor($days / 7);
        if ($weeks < 4) {
            return "Expirado hace {$weeks} semana" . ($weeks > 1 ? 's' : '');
        }

        $months = floor($days / 30);
        return "Expirado hace {$months} mes" . ($months > 1 ? 'es' : '');
    }

    public static function remainingText(Carbon $date): string
    {
        $now = Carbon::now();
        $days = $now->diffInDays($date);

        if ($days == 0) return 'Vence hoy';
        if ($days == 1) return 'Vence mañana';
        if ($days < 7) return "Vence en {$days} días";

        $weeks = floor($days / 7);
        if ($weeks < 4) {
            $remainingDays = $days % 7;
            $text = "Vence en {$weeks} semana" . ($weeks > 1 ? 's' : '');
            if ($remainingDays > 0) {
                $text .= " y {$remainingDays} día" . ($remainingDays > 1 ? 's' : '');
            }
            return $text;
        }

        $months = floor($days / 30);
        $remainingDays = $days % 30;
        $text = "Vence en {$months} mes" . ($months > 1 ? 'es' : '');
        if ($remainingDays > 0) {
            $text .= " y {$remainingDays} día" . ($remainingDays > 1 ? 's' : '');
        }
        return $text;
    }

    public static function expirationInfo(?Carbon $date): array
    {
        if (!$date) {
            return [
                'text' => null,
                'days' => null,
                'is_expired' => false,
                'is_today' => false,
                'urgency' => 'none',
            ];
        }

        $now = Carbon::now();
        $days = $now->diffInDays($date, false);

        return [
            'text' => self::expirationToHuman($date),
            'days' => $days,
            'is_expired' => $days < 0,
            'is_today' => $days == 0,
            'urgency' => self::urgencyLevel($days),
            'date_formatted' => $date->isoFormat('D [de] MMMM [de] YYYY'),
            'date_short' => $date->format('d/m/Y'),
        ];
    }

    public static function urgencyLevel(int $days): string
    {
        if ($days < 0) return 'vencido';
        if ($days == 0) return 'vencimiento_hoy';
        if ($days <= 3) return 'urgencia_alta';
        if ($days <= 7) return 'urgencia_media';
        return 'urgencia_baja';
    }

    public static function daysUntilDeletion(Carbon $deletedDate): int
    {
        $now = Carbon::now();
        if ($now->diffInDays($deletedDate) >= 30) {
            return 0;
        }

        $daysPassed = $deletedDate->diffInDays($now);

        $daysRemaining = 30 - $daysPassed;

        return max(0, (int) $daysRemaining);

    }

}
