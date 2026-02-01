<?php

namespace App\Imports;

use App\Core\Application\Services\Admin\AdminUsersServiceFacades;
use App\Models\User;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
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

    public function __construct(AdminUsersServiceFacades $adminService, User $user)
    {
        $this->adminService = $adminService;
        $this->user = $user;
    }

    public function collection(Collection $collection)
    {
        $rows = $collection->skip(1)->toArray();
        $importResponse = $this->adminService->importUsers($rows);
        $this->importResult = $importResponse->toArray();
    }
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function() {
                $result = $this->importResult ?: [
                    'summary' => [],
                    'errors' => [],
                    'warnings' => [],
                    'has_errors' => true,
                    'message' => 'El import terminó pero no se pudo generar el resumen.'
                ];
                $this->user->notify(new ImportFinishedNotification(
                    $result
                ));
            },
            ImportFailed::class => function(ImportFailed $event) {
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
}
