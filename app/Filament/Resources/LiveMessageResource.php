<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiveMessageResource\Pages;
use App\Models\LiveMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LiveMessageResource extends Resource
{
    protected static ?string $model = LiveMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';

    protected static ?string $navigationGroup = 'Lives';

    protected static ?string $navigationLabel = 'Messages de chat';

    protected static ?string $modelLabel = 'message';

    protected static ?string $pluralModelLabel = 'messages';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('live_id')
                    ->relationship('live', 'title')
                    ->disabled(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\Textarea::make('message')->disabled()->rows(4),
                Forms\Components\Select::make('type')
                    ->options([
                        'text' => 'Texte',
                        'gift' => 'Cadeau',
                        'system' => 'Système',
                    ])
                    ->disabled(),
                Forms\Components\TextInput::make('gift_type')->disabled(),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Auteur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('message')
                    ->wrap()
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('gift_type')
                    ->label('Cadeau')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Texte',
                        'gift' => 'Cadeau',
                        'system' => 'Système',
                    ]),
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
            'index' => Pages\ListLiveMessages::route('/'),
        ];
    }
}
