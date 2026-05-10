<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Questions & réponses';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('question')->required()->rows(4)->columnSpanFull(),
            Forms\Components\Select::make('type')
                ->options([
                    'single' => 'Choix unique',
                    'multiple' => 'Choix multiples',
                    'true_false' => 'Vrai / Faux',
                ])
                ->required(),
            Forms\Components\TextInput::make('points')->numeric()->default(1)->required(),
            Forms\Components\TextInput::make('order')->numeric()->default(0)->required(),
            Forms\Components\Repeater::make('answers')
                ->relationship()
                ->schema([
                    Forms\Components\Textarea::make('answer')->required()->rows(2),
                    Forms\Components\Toggle::make('is_correct')->label('Bonne réponse'),
                    Forms\Components\TextInput::make('order')->numeric()->default(0)->required(),
                ])
                ->columns(3)
                ->addActionLabel('Ajouter une réponse')
                ->collapsible()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                Tables\Columns\TextColumn::make('order')->sortable()->label('#'),
                Tables\Columns\TextColumn::make('question')->limit(60)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('points')->label('Pts'),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
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
}
