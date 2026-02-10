<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;

class TransactionReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Reports Generate';

    protected static string $view = 'filament.pages.transaction-report';

    // define default value
    public ?string $startDate = null;
    public ?string $endDate = null;

    // data transaksi
    public $transaction = [];

    // setting default start & end di filter calendar
    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Rentang waktu')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Tanggal Awal')
                            ->required(),

                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->minDate(fn($get) => $get('startDate')) // tanggal minimal
                    ]),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate PDF')
                ->action('generatePdf') // calling: generatePdf function
        ];
    }

    // generate pdf
    public function generatePdf()
    {
        // redirect ke route (supaya tidak terjadi error parsing)
        return redirect()->route('report.transactions.pdf', [
            'transaction' => $this->transaction,
            'start' => $this->startDate,
            'end' => $this->endDate
        ]);
    }
}
