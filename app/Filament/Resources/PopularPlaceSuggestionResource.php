<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopularPlaceSuggestionResource\Pages;
use App\Models\PopularPlace;
use App\Models\PopularPlaceSuggestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Tabs;

class PopularPlaceSuggestionResource extends Resource
{
    protected static ?string $model = PopularPlaceSuggestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Suggestions';

    public static function getLabel(): ?string
    {
        return __('Place Suggestion');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Place Suggestions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make(__('Suggestion Details'))
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label(__('Submitted By'))
                                    ->relationship('customer', 'name')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('title')
                                    ->label(__('Name'))
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
                                    ->label(__('Description'))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('address')
                                    ->label(__('Address'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('images')
                                    ->label(__('Images'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('place_suggestions')
                                    ->multiple()
                                    ->columnSpanFull()
                                    ->panelLayout('grid')
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/gif']),
                            ]),
                        Tabs\Tab::make(__('Location'))
                            ->schema([
                                Forms\Components\TextInput::make('lng')
                                    ->label(__('Longitude'))
                                    ->required(),
                                Forms\Components\TextInput::make('lat')
                                    ->label(__('Latitude'))
                                    ->required(),
                            ]),
                        Tabs\Tab::make(__('Status'))
                            ->schema([
                                Forms\Components\Placeholder::make('status')
                                    ->label(__('Current Status'))
                                    ->content(fn ($record) => $record?->status_label ?? __('Pending')),
                                                            ]),
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
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
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
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'hospital', 'pharmacy' => 'danger',
                        'bank', 'school' => 'info',
                        'park', 'market' => 'success',
                        'gas_station', 'metro_station' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Submitted By'))
                    ->searchable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('address')
                    ->label(__('Address'))
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('accepted')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        null => __('Pending'),
                        true => __('Accepted'),
                        false => __('Declined'),
                        default => __('Unknown'),
                    })
                    ->color(fn ($state): string => match ($state) {
                        null => 'warning',
                        true => 'success',
                        false => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\ImageColumn::make('preview_image')
                    ->label(__('Preview'))
                    ->getStateUsing(function ($record) {
                        if ($record->images && is_array($record->images) && count($record->images) > 0) {
                            return $record->images[0];
                        }
                        return null;
                    })
                    ->disk('public'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Submitted At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'accepted' => __('Accepted'),
                        'declined' => __('Declined'),
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value']) {
                            'pending' => $query->whereNull('accepted'),
                            'accepted' => $query->where('accepted', true),
                            'declined' => $query->where('accepted', false),
                            default => $query,
                        };
                    }),
                SelectFilter::make('type')
                    ->label(__('Type'))
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
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->accepted === null)
                    ->requiresConfirmation()
                    ->modalHeading(__('Approve Suggestion'))
                    ->modalDescription(__('This will create a new Popular Place with the same details and mark this suggestion as accepted.'))
                    ->action(function ($record) {
                        // Create PopularPlace with same details
                        $popularPlace = PopularPlace::create([
                            'title' => $record->title,
                            'description' => $record->description,
                            'images' => $record->images,
                            'address' => $record->address,
                            'lng' => $record->lng,
                            'lat' => $record->lat,
                            'type' => $record->type,
                        ]);

                        // Update suggestion as accepted
                        $record->update([
                            'accepted' => true,
                        ]);

                        Notification::make()
                            ->title(__('Suggestion Approved'))
                            ->body(__('Popular Place created successfully.'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('decline')
                    ->label(__('Decline'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->accepted === null)
                    ->requiresConfirmation()
                    ->modalHeading(__('Decline Suggestion'))
                    ->modalDescription(__('Are you sure you want to decline this suggestion?'))
                    ->action(function ($record) {
                        $record->update([
                            'accepted' => false,
                        ]);

                        Notification::make()
                            ->title(__('Suggestion Declined'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPopularPlaceSuggestions::route('/'),
            'create' => Pages\CreatePopularPlaceSuggestion::route('/create'),
            'view' => Pages\ViewPopularPlaceSuggestion::route('/{record}'),
            'edit' => Pages\EditPopularPlaceSuggestion::route('/{record}/edit'),
        ];
    }
}
