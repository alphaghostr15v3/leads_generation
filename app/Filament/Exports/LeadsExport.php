<?php

namespace App\Filament\Exports;

use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Excel;
use Filament\Forms\Components\CheckboxList;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LeadsExport extends ExcelExport
{
    public function getFormSchema(): array
    {
        return [
            CheckboxList::make('columns')
                ->label('Select Columns')
                ->options([
                    'id' => 'ID',
                    'name' => 'Name',
                    'address' => 'Address',
                    'phone' => 'Phone',
                    'website' => 'Website',
                    'review' => 'Review',
                    'created_at' => 'Created At',
                    'updated_at' => 'Updated At',
                ])
                ->default(['id', 'name', 'address', 'phone', 'website', 'review'])
                ->columns(2),
        ];
    }

    public function getOnly(): array
    {
        // Try multiple possible locations for column data
        $columns = $this->formData['columns'] 
            ?? $this->formData['excel']['columns'] 
            ?? $this->formData['pdf']['columns']
            ?? [];
        
        return $columns;
    }
    
    public function getColumns(): array
    {
        // Get all columns from parent (fromTable)
        $allColumns = parent::getColumns();
        
        // Get the selected columns
        $selectedColumns = $this->getOnly();
        
        // If no selection or empty, return all
        if (empty($selectedColumns)) {
            return $allColumns;
        }
        
        // Filter to only selected columns
        $filtered = [];
        foreach ($selectedColumns as $columnName) {
            if (isset($allColumns[$columnName])) {
                $filtered[$columnName] = $allColumns[$columnName];
            }
        }
        
        return $filtered;
    }
    
    protected function createFieldMappingFromTable(): \Illuminate\Support\Collection
    {
        $livewire = $this->getLivewire();
        $table = $livewire->getTable();
        
        // Get ALL columns, including hidden ones
        $columns = collect($table->getColumns());
        
        return $columns
            ->mapWithKeys(function (\Filament\Tables\Columns\Column $column) {
                $clonedCol = clone $column;
                $invadedColumn = \Livewire\invade($clonedCol);
                
                $exportColumn = \pxlrbt\FilamentExcel\Columns\Column::make($column->getName())
                    ->heading($column->getLabel())
                    ->getStateUsing($invadedColumn->getStateUsing)
                    ->tableColumn($clonedCol);
                
                rescue(fn () => $exportColumn->formatStateUsing($invadedColumn->formatStateUsing), report: false);
                
                return [
                    $column->getName() => $exportColumn,
                ];
            });
    }

    public function registerEvents(): array
    {
        if ($this->getWriterType() === Excel::DOMPDF) {
            return [
                AfterSheet::class => function (AfterSheet $event) {
                    $sheet = $event->sheet->getDelegate();
                    
                    // Page Setup
                    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                    $sheet->getPageSetup()->setFitToWidth(1);
                    $sheet->getPageSetup()->setFitToHeight(0);
                    
                    // Narrow Margins (in inches)
                    $sheet->getPageMargins()->setTop(0.5);
                    $sheet->getPageMargins()->setBottom(0.5);
                    $sheet->getPageMargins()->setLeft(0.5);
                    $sheet->getPageMargins()->setRight(0.5);
                    
                    // Get highest row and column
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();
                    
                    // Apply styling to all cells
                    $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);
                    
                    // Header styling (bold, centered)
                    $sheet->getStyle('A1:' . $highestColumn . '1')
                        ->getFont()->setBold(true);
                    $sheet->getStyle('A1:' . $highestColumn . '1')
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Add borders
                    $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                },
            ];
        }

        return [];
    }
}
