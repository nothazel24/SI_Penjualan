<?php

namespace App\Filament\Resources\ProductTransactionResource\Pages;

use App\Filament\Resources\ProductTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;
use App\Service\ProductTransactionService;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\ValidationException;

class EditProductTransaction extends EditRecord
{
    protected static string $resource = ProductTransactionResource::class;

    /*
        calling productTransactionService (update)
        NOTE : method ini meng-override auto-handling validation bawaan filament. jadi gunakan dengan tpat dawg
    */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(ProductTransactionService::class)
                ->update($record, $data);
        } catch (ValidationException $e) {
            // lempar notifikasi qty berlebih dari service. biar filament gk bengong saat update terjadi
            Notification::make()
                ->title('Gagal menyimpan')
                ->body($e->errors()['qty'][0] ?? 'Validasi gagal')
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected bool $isApprove = false;

    // catch flag / tanda yang dikiirim dan menyimpannya di livewire memory
    public function mount($record): void
    {
        parent::mount($record);
        $this->isApprove = request()->boolean('approve');
    }

    // ubah state transaksi
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->isApprove && filled($data['proof'])) {
            $data['is_paid'] = true;
        }

        return $data;
    }

    // save data kedalam database 
    protected function afterSave()
    {
        // jika kedua data ada
        if ($this->isApprove && $this->record->is_paid) {
            Notification::make()
                ->title('Transaksi berhasil')
                ->body('Transaksi atas nama ' . $this->record->name . ' berhasil disetujui')
                ->success()
                ->send();

            return ProductTransactionResource::getUrl('index');
        } else { // jika salah satu tidak sesuai syarat / keduanya kosong
            if (blank($this->record->proof)) {
                // lempar notifikasi
                Notification::make()
                    ->title('Transaksi gagal')
                    ->body('Silahkan isi data bukti dengan lengkap!')
                    ->danger()
                    ->send();

                throw new Halt(); // stop eksekusi program
            }
        }
    }

    // redirect ke halaman semula
    // protected function getRedirectUrl(): ?string
    // {
    //     return ProductTransactionResource::getUrl('index');
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            // Force delete (permanent) & restore
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
