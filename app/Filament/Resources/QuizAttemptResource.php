<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizAttemptResource\Pages;
use App\Models\QuizAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuizAttemptResource extends Resource
{
    protected static ?string $model = QuizAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Éducation';

    protected static ?string $navigationLabel = 'Tentatives de quiz';

    protected static ?string $modelLabel = 'tentative';

    protected static ?string $pluralModelLabel = 'tentatives';

    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\Select::make('quiz_id')
                    ->relationship('quiz', 'title')
                    ->disabled(),
                Forms\Components\TextInput::make('score')->numeric()->disabled(),
                Forms\Components\TextInput::make('max_score')->numeric()->disabled(),
                Forms\Components\Toggle::make('is_passed')->disabled(),
                Forms\Components\DateTimePicker::make('started_at')->disabled(),
                Forms\Components\DateTimePicker::make('completed_at')->disabled(),
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
            ->defaultSort('completed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->searchable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('score')->sortable(),
                Tables\Columns\TextColumn::make('max_score')->label('Max')->sortable(),
                Tables\Columns\TextColumn::make('score_percent')
                    ->label('%')
                    ->getStateUsing(fn (QuizAttempt $record): int => $record->score_percent),
                Tables\Columns\IconColumn::make('is_passed')
                    ->label('Réussi')
                    ->boolean(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Terminé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_passed')->label('Réussi'),
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
            'index' => Pages\ListQuizAttempts::route('/'),
        ];
    }
}
