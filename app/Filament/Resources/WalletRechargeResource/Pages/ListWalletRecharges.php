<?php

namespace App\Filament\Resources\WalletRechargeResource\Pages;

use App\Filament\Resources\WalletRechargeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWalletRecharges extends ListRecords
{
    protected static string $resource = WalletRechargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
