<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
            ->label('قبول')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->action(fn(Customer $record) => $record->update(['delivery_status' => 'approved']))
            ->visible(
                fn($record) => $record->delivery_status != 'approved'
            ),
            Actions\Action::make('block')
            ->label('حظر')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->action(fn(Customer $record) => $record->update(['delivery_status' => 'block']))
            ->visible(
                fn($record) => $record->delivery_status != 'block'
            ),
            Actions\Action::make('hold')
            ->label('إيقاف مؤقت')
            ->color('warning')
            ->icon('heroicon-o-exclamation-circle')
            ->action(fn(Customer $record) => $record->update(['delivery_status' => 'hold']))
            ->visible(
                fn($record) => $record->delivery_status != 'hold'
            ),
        ];
    }
}
