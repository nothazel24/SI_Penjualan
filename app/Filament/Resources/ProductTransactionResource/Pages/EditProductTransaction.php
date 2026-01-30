<?php

namespace App\Filament\Resources\ProductTransactionResource\Pages;

use App\Filament\Resources\ProductTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;
use App\Service\ProductTransactionService;
use Filament\Notifications\Notification;
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
