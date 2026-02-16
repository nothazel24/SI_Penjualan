<?php

namespace App\Filament\Pages;

use App\Models\ProductTransaction;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

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
        // validasi state
        $this->validate();

        // validasi data (jika kosong)
        $transaction = ProductTransaction::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay()
        ])->exists();

        // lempar notifikasi
        if (! $transaction) {
            Notification::make()
                ->title('Data tidak dapat ditemukan')
                ->body('Tidak ada data yang ditemukan dalam rentang waktu yang dipilih')
                ->danger()
                ->send();

            return;
        }

        // redirect ke route (supaya tidak terjadi error parsing)
        return redirect()->route('report.transactions.pdf', [
            'transaction' => $this->transaction,
            'start' => $this->startDate,
            'end' => $this->endDate
        ]);
    }
}
