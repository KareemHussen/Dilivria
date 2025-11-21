<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?int $navigationSort = 7;
    public static function getLabel(): ?string
    {
        return __('settings.settings');
    }
    public static function getPluralLabel(): ?string
    {
        return __('settings.settings');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('delivery_coverage')
                    ->label(__("Delivery Coverage"))
                    ->required()
                    ->prefix('Km')
                    ->numeric(),
                Forms\Components\TextInput::make('company_share')
                    ->label(__("Company's Share"))
                    ->required()
                    ->prefix(__('EGP'))
                    ->numeric(),
                Forms\Components\TextInput::make('cost_per_km')
                    ->label(__("Cost\Km"))
                    ->required()
                    ->prefix(__('EGP'))
                    ->numeric(),
                Forms\Components\TextInput::make('phone')
                    ->label(__('settings.contact_phone'))
                    ->tel()
                    ->required()
                    ->helperText(__('settings.phone_helper'))
                    ->placeholder('+201012345678'),
                Forms\Components\TextInput::make('address')
                    ->label(__('settings.company_address'))
                    ->required()
                    ->helperText(__('settings.address_helper'))
                    ->placeholder('@example123')
                    ->regex('/^@[a-zA-Z0-9_]{3,}$/')
                    ->validationMessages([
                        'regex' => 'The instapay address must start with @ followed by at least 3 alphanumeric characters or underscores',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('delivery_coverage')
                    ->label(__("Delivery Coverage"))
                    ->numeric()
                    ->suffix(" " . __('Km')),
                Tables\Columns\TextColumn::make('company_share')
                    ->label(__("Company's Share"))
                    ->numeric()
                    ->money("EGP"),
                Tables\Columns\TextColumn::make('cost_per_km')
                    ->label(__("Cost\Km"))
                    ->numeric()
                    ->money("EGP"),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('settings.contact_phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label(__('settings.company_address'))
                    ->wrap()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'view' => Pages\ViewSetting::route('/{record}'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
