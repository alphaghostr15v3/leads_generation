<?php

namespace App\Filament\Resources\Leads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use App\Filament\Actions\DataPassExportAction;
use Maatwebsite\Excel\Excel;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\LeadsExport;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                DataPassExportAction::make()
                    ->exports([
                        LeadsExport::make('excel')
                            ->fromTable()
                            ->withFilename('leads-' . date('Y-m-d'))
                            ->withWriterType(Excel::XLSX)
                            ->label('Download Excel'),
                        LeadsExport::make('pdf')
                            ->fromTable()
                            ->withFilename('leads-' . date('Y-m-d'))
                            ->withWriterType(Excel::DOMPDF)
                            ->label('Download PDF'),
                    ])
                    ->label('Export'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('address')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('website')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('review')
                    ->limit(50)
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100, 'all']);
    }
}
