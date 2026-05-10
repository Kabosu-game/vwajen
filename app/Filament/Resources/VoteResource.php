<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoteResource\Pages;
use App\Models\Vote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoteResource extends Resource
{
    protected static ?string $model = Vote::class;
    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationGroup = 'Démocratie participative';
    protected static ?string $navigationLabel = 'Votes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\Repeater::make('gallery')
                ->label('Galerie (illimitée : images, vidéos, audios)')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'image' => 'Image',
                            'video' => 'Vidéo',
                            'audio' => 'Audio',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('url')
                        ->label('URL du média')
                        ->required()
                        ->maxLength(2000),
                ])
                ->columns(2)
                ->collapsible()
                ->columnSpanFull()
                ->defaultItems(0),

            Forms\Components\Select::make('status')
                ->options(['draft' => 'Brouillon', 'active' => 'Actif', 'closed' => 'Clôturé'])
                ->required()->columnSpan(1),
            Forms\Components\Toggle::make('is_published')->label('Publié')->columnSpan(1),
            Forms\Components\Toggle::make('is_anonymous')->label('Anonyme')->columnSpan(1),
            Forms\Components\DateTimePicker::make('start_date')->columnSpan(1),
            Forms\Components\DateTimePicker::make('end_date')->required()->columnSpan(1),

            Forms\Components\Repeater::make('options')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('label')->required(),
                    Forms\Components\TextInput::make('description'),
                    Forms\Components\TextInput::make('order')->numeric()->default(0),
                ])
                ->columnSpanFull()
                ->label('Options de vote'),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['secondary' => 'draft', 'success' => 'active', 'danger' => 'closed']),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Publié'),
                Tables\Columns\TextColumn::make('total_votes_count')->label('Votes'),
                Tables\Columns\TextColumn::make('end_date')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVotes::route('/'),
            'create' => Pages\CreateVote::route('/create'),
            'edit'   => Pages\EditVote::route('/{record}/edit'),
        ];
    }
}
