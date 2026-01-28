<?php

namespace App\Filament\Resources\ProductTransactionResource\Pages;

use App\Filament\Resources\ProductTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;
use App\Service\ProductTransactionService;

class EditProductTransaction extends EditRecord
{
    protected static string $resource = ProductTransactionResource::class;

    // calling productTransactionService (update)
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductTransactionService::class)
            ->update($record, $data);
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
