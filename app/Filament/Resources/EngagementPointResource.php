<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EngagementPointResource\Pages;
use App\Models\EngagementPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EngagementPointResource extends Resource
{
    protected static ?string $model = EngagementPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Adhésion & engagement';

    protected static ?string $navigationLabel = 'Points d\'engagement';

    protected static ?string $modelLabel = 'entrée de points';

    protected static ?string $pluralModelLabel = 'historique des points';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('points')->numeric()->disabled(),
                Forms\Components\TextInput::make('action')->disabled(),
                Forms\Components\TextInput::make('pointable_type')->disabled(),
                Forms\Components\TextInput::make('pointable_id')->numeric()->disabled(),
                Forms\Components\Textarea::make('description')->disabled()->rows(3),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('points')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pointable_type')
                    ->label('Objet')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : ''),
                Tables\Columns\TextColumn::make('pointable_id')->label('ID objet'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListEngagementPoints::route('/'),
        ];
    }
}
