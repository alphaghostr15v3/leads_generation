<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LeadsExport implements FromCollection, WithHeadings, WithEvents
{
    protected $leads;
    protected $columns;
    protected $isPdf;

    public function __construct($leads, $columns, $isPdf = false)
    {
        $this->leads = $leads;
        $this->columns = $columns;
        $this->isPdf = $isPdf;
    }

    public function collection()
    {
        return $this->leads->map(function ($lead) {
            $row = [];
            foreach ($this->columns as $column) {
                $row[] = $lead->$column ?? '';
            }
            return $row;
        });
    }

    public function headings(): array
    {
        return array_map(function($column) {
            return ucwords(str_replace('_', ' ', $column));
        }, $this->columns);
    }

    public function registerEvents(): array
    {
        if (!$this->isPdf) {
            return [];
        }

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Page Setup for PDF
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                
                // Narrow Margins
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
}
