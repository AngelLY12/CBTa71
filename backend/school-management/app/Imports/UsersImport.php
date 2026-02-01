<?php

namespace App\Imports;

use App\Core\Application\Services\Admin\AdminUsersServiceFacades;
use App\Models\User;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class UsersImport implements ToCollection, ShouldQueue, WithEvents, WithChunkReading
{
    protected AdminUsersServiceFacades $adminService;
    protected User $user;
    private array $importResult = [];
    private string $filePath = '';

    public function __construct(AdminUsersServiceFacades $adminService, User $user, string $filePath = '')
    {
        $this->adminService = $adminService;
        $this->user = $user;
        $this->filePath = $filePath;
    }

    public function collection(Collection $collection)
    {
        try {
            Log::info("Processing chunk with ".count($collection)." rows, filePath={$this->filePath}");
            $rows = $collection->skip(1)->toArray();
            $importResponse = $this->adminService->importUsers($rows);
            $this->importResult = $importResponse->toArray();
            Log::info("Chunk processed successfully.");
        } catch (\Throwable $e) {
            Log::error("Error processing chunk: ".$e->getMessage(), [
                'exception' => $e,
                'filePath' => $this->filePath,
            ]);
            throw $e;
        }
    }
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function() {
                Log::info("AfterImport event triggered, filePath={$this->filePath}");
                $this->cleanupFile();
                $result = $this->importResult ?: [
                    'summary' => [],
                    'errors' => [],
                    'warnings' => [],
                    'has_errors' => true,
                    'message' => 'El import terminó pero no se pudo generar el resumen.'
                ];
                $this->user->notify(new ImportFinishedNotification($result));
            },
            ImportFailed::class => function(ImportFailed $event) {
                Log::error("ImportFailed event triggered, filePath={$this->filePath}", [
                    'exception' => $event->getException()
                ]);
                $this->cleanupFile();
                $this->user->notify(new ImportFailedNotification(
                    $event->getException()->getMessage()
                ));
            }
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
    private function cleanupFile(): void
    {
        if ($this->filePath) {
            $file = $this->filePath;
            try {
                Log::info("Scheduling cleanup for file: {$file}");
                // Simplemente borra directo si no necesitas delay
                Storage::disk('local')->delete($file);
                Log::info("File deleted: {$file}");
            } catch (\Throwable $e) {
                Log::error("Error deleting file: ".$e->getMessage(), ['filePath' => $file]);
            }
        }
    }
}
