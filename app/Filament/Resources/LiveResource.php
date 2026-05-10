<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LiveResource\Pages;
use App\Models\Live;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LiveResource extends Resource
{
    protected static ?string $model = Live::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'Lives';

    protected static ?string $navigationLabel = 'Diffusions en direct';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Diffuseur')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),

            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),

            Forms\Components\FileUpload::make('thumbnail')
                ->image()
                ->disk('public')
                ->directory('lives/thumbnails')
                ->columnSpanFull(),

            Forms\Components\Select::make('type')
                ->options([
                    'discussion' => 'Discussion',
                    'debat' => 'Débat',
                    'campagne' => 'Campagne',
                    'information' => 'Information',
                    'autre' => 'Autre',
                ])
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'scheduled' => 'Programmé',
                    'live' => 'En direct',
                    'ended' => 'Terminé',
                    'cancelled' => 'Annulé',
                ])
                ->required(),

            Forms\Components\TextInput::make('stream_key')->maxLength(255)->columnSpan(1),
            Forms\Components\TextInput::make('stream_url')->url()->columnSpanFull(),
            Forms\Components\TextInput::make('playback_url')->url()->columnSpanFull(),
            Forms\Components\Toggle::make('is_recorded')->default(true),

            Forms\Components\DateTimePicker::make('scheduled_at'),
            Forms\Components\DateTimePicker::make('started_at'),
            Forms\Components\DateTimePicker::make('ended_at'),

            Forms\Components\TextInput::make('viewers_count')->numeric()->default(0),
            Forms\Components\TextInput::make('peak_viewers')->numeric()->default(0),
            Forms\Components\TextInput::make('likes_count')->numeric()->default(0),
            Forms\Components\TextInput::make('recording_url')->url()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Diffuseur')->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40)->wrap(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('type')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('scheduled_at')->dateTime('d/m/Y H:i')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('viewers_count')->label('Spectateurs')->sortable(),
                Tables\Columns\TextColumn::make('peak_viewers')->label('Pic viewers')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Programmé',
                        'live' => 'En direct',
                        'ended' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('type'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLives::route('/'),
            'create' => Pages\CreateLive::route('/create'),
            'edit' => Pages\EditLive::route('/{record}/edit'),
        ];
    }
}
