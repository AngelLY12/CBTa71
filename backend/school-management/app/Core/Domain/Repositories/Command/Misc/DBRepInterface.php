<?php

namespace App\Core\Domain\Repositories\Command\Misc;

interface DBRepInterface
{
    public function checkDBStatus(): bool;
}
