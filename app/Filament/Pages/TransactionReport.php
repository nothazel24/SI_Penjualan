<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

use Carbon\Carbon;
use Filament\Forms\Components\Fieldset;

class TransactionReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Laporan Transaksi';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.transaction-report'; // ini custom dashboard UI lee

    public ?string $filter_type = 'monthly'; // define default value untuk filter (bulanan)

    // define default value = null
    public ?string $start_week_date = null;
    public ?string $end_week_date = null;

    public ?string $start_month = null;
    public ?string $end_month = null;

    public function mount()
    {
        $this->start_month = now()->format('Y-m');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('filter_type')
                    ->label('Jenis Laporan')
                    ->options([
                        'weekly' => 'Per Minggu',
                        'monthly' => 'Per Bulan',
                    ])
                    ->default('monthly')
                    ->live(),

                Fieldset::make('Filter waktu')
                    ->schema([
                        // filter mingguan
                        DatePicker::make('start_week_date')
                            ->label('Tanggal Awal (Mingguan)')
                            ->visible(fn($get) => $get('filter_type') === 'weekly')
                            ->required(fn($get) => $get('filter_type') === 'weekly'),

                        DatePicker::make('end_week_date')
                            ->label('Tanggal Akhir (Mingguan)')
                            ->visible(fn($get) => $get('filter_type') === 'weekly')
                            ->required(fn($get) => $get('filter_type') === 'weekly')
                            ->minDate(fn($get) => $get('start_week_date')),

                        // filter bulanan
                        DatePicker::make('start_month')
                            ->label('Bulan Awal')
                            ->displayFormat('F Y')
                            ->visible(fn($get) => $get('filter_type') === 'monthly')
                            ->required(fn($get) => $get('filter_type') === 'monthly'),

                        DatePicker::make('end_month')
                            ->label('Bulan Akhir')
                            ->displayFormat('F Y')
                            ->visible(fn($get) => $get('filter_type') === 'monthly')
                            ->required(fn($get) => $get('filter_type') === 'monthly')
                            ->minDate(fn($get) => $get('start_month')),
                    ]),
            ]);
    }


    protected function getActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate PDF')
                ->action('generatePdf')
                ->color('primary')
                ->openUrlInNewTab(),
        ];
    }

    public function generatePdf()
    {
        // send data filter ke blade file resource/views/reports/transaction.blade.php
        return redirect()->route('reports.transactions', [
            'type' => $this->filter_type,
            'start_week_date' => $this->start_week_date,
            'end_week_date'   => $this->end_week_date,
            'start_month' => $this->start_month,
            'end_month'   => $this->end_month,
        ]);
    }
}
