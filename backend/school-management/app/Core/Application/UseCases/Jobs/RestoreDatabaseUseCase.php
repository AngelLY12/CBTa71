<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Command\DBRepInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class RestoreDatabaseUseCase
{
    public function __construct(private DBRepInterface $rep)
    {
    }

    public function execute(): bool
    {
        try {
            $check = $this->rep->checkDBStatus();
            if ($check) {
                Log::channel('stderr')->info('La base de datos está activa, no es necesario restaurar.');
                return true;
            }
        } catch (\Exception $e) {
            Log::channel('stderr')->warning('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }

        Log::channel('stderr')->warning('Restaurando la última copia de seguridad...');

        $files = collect(Storage::disk('google')->allFiles())
        ->filter(fn($f) => str_ends_with(strtolower($f), '.zip'))
        ->sortDesc()
        ->values();

        Log::channel('stderr')->info('Archivos en Google Drive:', $files->toArray());
        if ($files->isEmpty()) {
            Log::channel('stderr')->error('No hay respaldos disponibles en Google Drive.');
            return false;
        }

        $latestBackup = $files->first();
        Log::channel('stderr')->info("Descargando respaldo: {$latestBackup}");

        $localPath = storage_path('app/restore.zip');
        try {
            $content = Storage::disk('google')->get($latestBackup);
            file_put_contents($localPath, $content);
        } catch (\Exception $e) {
            Log::channel('stderr')->error('Error al descargar el respaldo: ' . $e->getMessage());
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
            Log::channel('stderr')->error('No se pudo abrir el archivo ZIP');
            return false;
        }

        $directory = new RecursiveDirectoryIterator($restoreDir);
        $iterator  = new RecursiveIteratorIterator($directory);
        $sqlFiles  = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'sql') {
                $sqlFiles[] = $file->getPathname();
            }
        }

        if (empty($sqlFiles)) {
            Log::channel('stderr')->error('No se encontró un archivo SQL para restaurar.');
            return false;
        }

        $sqlFile = $sqlFiles[0];

        try {
            $sqlContent = file_get_contents($sqlFile);
            DB::unprepared($sqlContent);
            Log::channel('stderr')->info('Base de datos restaurada correctamente.');
        } catch (\Exception $e) {
            Log::channel('stderr')->error('Error al ejecutar el SQL: ' . $e->getMessage());
            return false;
        }

        return true;

    }
}
