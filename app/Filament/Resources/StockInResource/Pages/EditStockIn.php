<?php

namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockIn extends EditRecord
{
    protected static string $resource = StockInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
