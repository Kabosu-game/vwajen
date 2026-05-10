<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DatabaseNotificationResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Notifications (in-app)';

    protected static ?string $modelLabel = 'notification';

    protected static ?string $pluralModelLabel = 'notifications';

    protected static ?string $slug = 'notifications-in-app';

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->label('Classe notification')
                    ->disabled(),
                Forms\Components\TextInput::make('notifiable_type')
                    ->disabled(),
                Forms\Components\TextInput::make('notifiable_id')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Textarea::make('data')
                    ->label('Données (JSON)')
                    ->disabled()
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null || $state === '') {
                            return '';
                        }
                        $decoded = json_decode($state, true);

                        return is_array($decoded)
                            ? (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : $state;
                    }),
                Forms\Components\TextInput::make('read_at')
                    ->disabled(),
                Forms\Components\TextInput::make('created_at')
                    ->disabled(),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->searchable()
                    ->formatStateUsing(function (?string $state): string {
                        if (! $state) {
                            return '';
                        }

                        return class_basename($state);
                    }),
                Tables\Columns\TextColumn::make('destinataire')
                    ->label('Destinataire')
                    ->getStateUsing(function (DatabaseNotification $record): string {
                        $n = $record->notifiable;
                        if ($n !== null && isset($n->name)) {
                            return (string) $n->name;
                        }

                        return class_basename((string) $record->notifiable_type).' #'.$record->notifiable_id;
                    }),
                Tables\Columns\IconColumn::make('read_at')
                    ->label('Lu')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Envoyée le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lecture')
                    ->label('Lecture')
                    ->options([
                        'read' => 'Lues',
                        'unread' => 'Non lues',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'read' => $query->whereNotNull('read_at'),
                            'unread' => $query->whereNull('read_at'),
                            default => $query,
                        };
                    }),
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
            'index' => Pages\ListDatabaseNotifications::route('/'),
        ];
    }
}
