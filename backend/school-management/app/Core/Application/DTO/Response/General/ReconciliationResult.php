<?php

namespace App\Core\Application\DTO\Response\General;

class ReconciliationResult
{
    public function __construct(
        public int $processed = 0,
        public int $updated = 0,
        public int $notified = 0,
        public int $failed = 0,
    )
    {}

}
