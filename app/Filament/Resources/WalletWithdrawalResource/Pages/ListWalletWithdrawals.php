<?php

namespace App\Filament\Resources\WalletWithdrawalResource\Pages;

use App\Filament\Resources\WalletWithdrawalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWalletWithdrawals extends ListRecords
{
    protected static string $resource = WalletWithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
