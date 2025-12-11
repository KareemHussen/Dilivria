<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopularPlaceResource\Pages;
use App\Filament\Resources\PopularPlaceResource\RelationManagers;
use App\Forms\Components\GoogleMapField;
use App\Models\PopularPlace;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Tabs;

class PopularPlaceResource extends Resource
{
    protected static ?string $model = PopularPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function getLabel(): ?string
    {
        return __('Popular Place');  // Translation function works here
    }
    public static function getPluralLabel(): ?string
    {
        return __("Popular Places");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make(__('Place Informations'))
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__("Name"))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('type')
                                    ->label(__('Type'))
                                    ->searchable()
                                    ->options([
                                        'restaurant' => __('Restaurant'),
                                        'pharmacy' => __('Pharmacy'),
                                        'market' => __('Market'),
                                        'gas_station' => __('Gas Station'),
                                        'metro_station' => __('Metro Station'),
                                        'hospital' => __('Hospital'),
                                        'bank' => __('Bank'),
                                        'school' => __('School'),
                                        'mall' => __('Mall'),
                                        'cafe' => __('Cafe'),
                                        'hotel' => __('Hotel'),
                                        'park' => __('Park'),
                                        'cinema' => __('Cinema'),
                                        'other' => __('Other'),
                                    ]),
                                Forms\Components\Textarea::make('description')
                                    ->label(__("Description"))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('address')
                                            ->label(__('Address'))
                                            ->required()
                                            ->maxLength(255),
                                Forms\Components\FileUpload::make('images')
                                    ->label(__("Images"))
                                    ->image()
                                    ->disk('public')
                                    ->directory('places')
                                    ->multiple()
                                    ->columnSpanFull()
                                    ->panelLayout('grid')
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/gif']),
                                ]),
                                Tabs\Tab::make(__("Location"))
                                ->schema([
                                    GoogleMapField::make('location')
                                    ->label(__("Location"))
                                    ->apiKey(env('GOOGLE_MAP_KEY'))
                                    ->reactive()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        $set('lng', $state->detail->lng);
                                        $set('lat', $state->detail->lat);
                                    })
                                    ->latField('lat')    // Bind to the 'lat' field
                                    ->lngField('lng'),
                                    Forms\Components\TextInput::make('lng')
                                        ->label(__('Longitude'))
                                        ->reactive()
                                        ->required(),
                                    Forms\Components\TextInput::make('lat')
                                        ->label(__('Latitude'))
                                        ->reactive()
                                        ->required(),
                            ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'restaurant' => __('Restaurant'),
                        'pharmacy' => __('Pharmacy'),
                        'market' => __('Market'),
                        'gas_station' => __('Gas Station'),
                        'metro_station' => __('Metro Station'),
                        'hospital' => __('Hospital'),
                        'bank' => __('Bank'),
                        'school' => __('School'),
                        'mall' => __('Mall'),
                        'cafe' => __('Cafe'),
                        'hotel' => __('Hotel'),
                        'park' => __('Park'),
                        'cinema' => __('Cinema'),
                        'other' => __('Other'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'hospital', 'pharmacy' => 'danger',
                        'bank', 'school' => 'info',
                        'park', 'market' => 'success',
                        'gas_station', 'metro_station' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('address')
                    ->label(__('Address'))
                    ->searchable(),
                Tables\Columns\ImageColumn::make('preview_image')
                    ->label(__('Preview'))
                    ->getStateUsing(function ($record) {
                        if ($record->images && is_array($record->images) && count($record->images) > 0) {
                            return $record->images[0]; // Return first image
                        }
                        return null;
                    })
                    ->disk('public'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Creation Date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPopularPlaces::route('/'),
            'create' => Pages\CreatePopularPlace::route('/create'),
            'edit' => Pages\EditPopularPlace::route('/{record}/edit'),
        ];
    }
}
