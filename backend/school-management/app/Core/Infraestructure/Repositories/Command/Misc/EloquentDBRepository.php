<?php

namespace App\Core\Infraestructure\Repositories\Command\Misc;

use App\Core\Domain\Repositories\Command\Misc\DBRepInterface;
use Illuminate\Support\Facades\DB;

class EloquentDBRepository implements DBRepInterface
{
    public function checkDBStatus(): bool
    {
        DB::connection()->getPdo();
        $tables = DB::select('SHOW TABLES');
        return count($tables) > 0;
    }
}
