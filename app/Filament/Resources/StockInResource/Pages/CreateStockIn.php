<?php

namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStockIn extends CreateRecord
{
    protected static string $resource = StockInResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();;
        $data['transaction_type'] = 'purchase';
        return $data;


    }

}
