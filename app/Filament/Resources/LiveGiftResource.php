<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiveGiftResource\Pages;
use App\Models\LiveGift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LiveGiftResource extends Resource
{
    protected static ?string $model = LiveGift::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Lives';

    protected static ?string $navigationLabel = 'Cadeaux en direct';

    protected static ?string $modelLabel = 'cadeau';

    protected static ?string $pluralModelLabel = 'cadeaux';

    protected static ?int $navigationSort = 41;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('live_id')
                    ->relationship('live', 'title')
                    ->disabled(),
                Forms\Components\Select::make('sender_id')
                    ->relationship('sender', 'name')
                    ->label('Envoyeur')
                    ->disabled(),
                Forms\Components\TextInput::make('gift_type')->disabled(),
                Forms\Components\TextInput::make('value')->numeric()->disabled(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('live.title')
                    ->label('Live')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Envoyeur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gift_type')->label('Type')->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListLiveGifts::route('/'),
        ];
    }
}
