<?php

namespace App\Core\Domain\Utils\Validators;

use App\Exceptions\Conflict\PromotionAlreadyExecutedException;
use App\Exceptions\NotAllowed\PromotionNotAllowedException;

class SemesterPromotionValidator
{
    public static function ensurePromotionIsValid(bool $wasExecuted)
    {
        $allowedMonths = config('promotions.allowed_months');
        $currentMonth = now()->month;

        if (! in_array($currentMonth, $allowedMonths)) {
            throw new PromotionNotAllowedException($allowedMonths);
        }

        if ($wasExecuted) {
            throw new PromotionAlreadyExecutedException();
        }
    }

}
