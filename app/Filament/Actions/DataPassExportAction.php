<?php

namespace App\Filament\Actions;

use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class DataPassExportAction extends ExportAction
{
    public function handleExport(array $data)
    {
        $exportable = $this->getSelectedExport($data);
        $livewire = $this->getLivewire();
        
        // Extract the export-specific data
        $exportName = $exportable->getName();
        $exportData = $data[$exportName] ?? [];
        
        // Merge with top-level data (for backward compatibility)
        $formData = array_merge($data, $exportData);

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'records' => property_exists($livewire, 'record') ? collect([$livewire->record]) : null,
            'formData' => $formData, 
        ])->export();
    }
}
