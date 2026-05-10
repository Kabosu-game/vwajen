<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SavedContentResource\Pages;
use App\Models\SavedContent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SavedContentResource extends Resource
{
    protected static ?string $model = SavedContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Bibliothèque (favoris)';

    protected static ?string $modelLabel = 'enregistrement';

    protected static ?string $pluralModelLabel = 'enregistrements';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saveable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : ''),
                Tables\Columns\TextColumn::make('saveable_id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enregistré le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSavedContents::route('/'),
        ];
    }
}
