<?php

namespace App\Imports;

use App\Core\Application\DTO\Response\General\ImportResponse;
use App\Core\Application\Services\Admin\AdminServiceFacades;
use App\Models\User;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class StudentDetailsImport implements ToCollection, ShouldQueue, WithEvents
{
    protected AdminServiceFacades $adminService;
    protected User $user;
    private array $importResult = [];

    public function __construct(AdminServiceFacades $adminService, User $user)
    {
        $this->adminService = $adminService;
        $this->user = $user;
    }
    /**
    * @param Collection $collection
    */

    public function collection(Collection $collection)
    {
        $rows = $collection->skip(1)->toArray();
        $importResponse = $this->adminService->importStudents($rows);
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
}
