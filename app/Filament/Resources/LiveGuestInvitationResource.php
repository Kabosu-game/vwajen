<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiveGuestInvitationResource\Pages;
use App\Models\LiveGuestInvitation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LiveGuestInvitationResource extends Resource
{
    protected static ?string $model = LiveGuestInvitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Lives';

    protected static ?string $navigationLabel = 'Invitations invités';

    protected static ?string $modelLabel = 'invitation';

    protected static ?string $pluralModelLabel = 'invitations';

    protected static ?int $navigationSort = 42;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('live_id')
                    ->relationship('live', 'title')
                    ->searchable()
                    ->disabled(),
                Forms\Components\Select::make('inviter_id')
                    ->relationship('inviter', 'name')
                    ->label('Inviteur')
                    ->searchable()
                    ->disabled(),
                Forms\Components\Select::make('invitee_id')
                    ->relationship('invitee', 'name')
                    ->label('Invité')
                    ->searchable()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        LiveGuestInvitation::STATUS_PENDING => 'En attente',
                        LiveGuestInvitation::STATUS_ACCEPTED => 'Acceptée',
                        LiveGuestInvitation::STATUS_DECLINED => 'Refusée',
                        LiveGuestInvitation::STATUS_REVOKED => 'Révoquée',
                    ])
                    ->required(),
            ]);
    }

    public static function canCreate(): bool
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
                    ->limit(28),
                Tables\Columns\TextColumn::make('inviter.name')
                    ->label('Inviteur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invitee.name')
                    ->label('Invité')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        LiveGuestInvitation::STATUS_PENDING => 'En attente',
                        LiveGuestInvitation::STATUS_ACCEPTED => 'Acceptée',
                        LiveGuestInvitation::STATUS_DECLINED => 'Refusée',
                        LiveGuestInvitation::STATUS_REVOKED => 'Révoquée',
                        default => (string) $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        LiveGuestInvitation::STATUS_PENDING => 'En attente',
                        LiveGuestInvitation::STATUS_ACCEPTED => 'Acceptée',
                        LiveGuestInvitation::STATUS_DECLINED => 'Refusée',
                        LiveGuestInvitation::STATUS_REVOKED => 'Révoquée',
                    ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiveGuestInvitations::route('/'),
            'edit' => Pages\EditLiveGuestInvitation::route('/{record}/edit'),
        ];
    }
}
