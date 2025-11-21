<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MiscPagesResource\Pages;
use App\Filament\Resources\MiscPagesResource\RelationManagers;
use App\Models\MiscPages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\KeyValue;

class MiscPagesResource extends Resource
{
    protected static ?string $model = MiscPages::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 6;


    public static function getLabel(): ?string
    {
        return __('Pages');  // Translation function works here
    }
    public static function getPluralLabel(): ?string
    {
        return __('Pages');  // For plural label translations
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('about')
                    ->label(__("About Page"))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('privacy_terms')
                    ->label(__("Privacy and Terms Page"))
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('faq')
                    ->label(__("FaQ Page"))
                    ->keyLabel(__("Question"))
                    ->valueLabel(__('Answer'))
                    ->helperText(__('From FaQ you can add questions and their answers'))
                    ->addButtonLabel(__('Add Question'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('contact_us')
                    ->label(__("Contact Method"))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('about')
                    ->label(__("About Page"))
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('privacy_terms')
                    ->label(__("Privacy and Terms Page"))
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_us')
                    ->label(__("Contact Method"))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__("Creation Date"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListMiscPages::route('/'),
            'create' => Pages\CreateMiscPages::route('/create'),
            'view' => Pages\ViewMiscPages::route('/{record}'),
            'edit' => Pages\EditMiscPages::route('/{record}/edit'),
        ];
    }
}
