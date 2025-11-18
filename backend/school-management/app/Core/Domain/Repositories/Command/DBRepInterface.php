<?php

namespace App\Core\Domain\Repositories\Command;

interface DBRepInterface
{
    public function checkDBStatus(): bool;
}
