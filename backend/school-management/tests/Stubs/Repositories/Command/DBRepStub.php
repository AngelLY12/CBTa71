<?php

namespace Tests\Stubs\Repositories\Command;
use App\Core\Domain\Repositories\Command\Misc\DBRepInterface;

class DBRepStub implements DBRepInterface
{
    private bool $dbStatus = true;
    private bool $throwConnectionError = false;
    private int $tablesCount = 5;

    public function checkDBStatus(): bool
    {
        if ($this->throwConnectionError) {
            throw new \RuntimeException('Database connection error');
        }

        return $this->dbStatus && $this->tablesCount > 0;
    }

    public function setDBStatus(bool $status): self
    {
        $this->dbStatus = $status;
        return $this;
    }

    public function shouldThrowConnectionError(bool $throw = true): self
    {
        $this->throwConnectionError = $throw;
        return $this;
    }

    public function setTablesCount(int $count): self
    {
        $this->tablesCount = $count;
        return $this;
    }
}
