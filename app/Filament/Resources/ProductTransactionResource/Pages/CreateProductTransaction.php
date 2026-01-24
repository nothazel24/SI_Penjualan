<?php

namespace App\Filament\Resources\ProductTransactionResource\Pages;

use App\Filament\Resources\ProductTransactionResource;
use App\Service\ProductTransactionService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProductTransaction extends CreateRecord
{
    protected static string $resource = ProductTransactionResource::class;

    // calling productTransactionService
    protected function handleRecordCreation(array $data): Model
    {
        return app(ProductTransactionService::class)
            ->create($data);
    }
}
