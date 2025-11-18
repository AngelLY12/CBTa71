<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Command\DBRepInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RestoreDatabaseUseCase
{
    public function __construct(private DBRepInterface $rep)
    {
    }

    public function execute(): bool
    {
        try{
            $check=$this->rep->checkDBStatus();
            if($check)
            {
                Log::info('La base de datos está activa, no es necesario restaurar.');
                return true;
            }
        }catch (\Exception $e) {
            Log::warn('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        Log::warn('Restaurando la última copia de seguridad...');
       $files = collect(Storage::disk('google')->files())
            ->filter(fn($f) => str_ends_with($f, '.zip'))
            ->sortDesc()
            ->values();

        if ($files->isEmpty()) {
            Log::error('No hay respaldos disponibles en Google Drive.');
            return false;
        }

        $latestBackup = $files->first();
        Log::info("Descargando respaldo: {$latestBackup}");

        $localPath = storage_path('app/restore.zip');
         try {
            Storage::disk('google')->download($latestBackup, $localPath);
        } catch (\Exception $e) {
            Log::error('Error al descargar el respaldo: ' . $e->getMessage());
            return false;
        }
        $restoreDir = storage_path('app/restore');
        if (!file_exists($restoreDir)) {
            mkdir($restoreDir, 0755, true);
        }
        $zip = new ZipArchive();
        if ($zip->open($localPath) === true) {
            $zip->extractTo($restoreDir);
            $zip->close();
        } else {
            Log::error('No se pudo abrir el archivo ZIP');
            return false;
        }

        $sqlFiles = glob($restoreDir . '/*.sql');
        if (empty($sqlFiles)) {
            Log::error('No se encontró un archivo SQL para restaurar.');
            return false;
        }

        $sqlFile = $sqlFiles[0];

        try {
            $sqlContent = file_get_contents($sqlFile);
            DB::unprepared($sqlContent);
            Log::info('Base de datos restaurada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al ejecutar el SQL: ' . $e->getMessage());
            return false;
        }

        return true;

    }
}
