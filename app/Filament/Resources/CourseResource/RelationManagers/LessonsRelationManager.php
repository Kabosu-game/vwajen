<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Leçons';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\Textarea::make('content')->rows(8)->columnSpanFull(),
            Forms\Components\TextInput::make('video_url')->url()->columnSpanFull(),
            Forms\Components\TextInput::make('duration_minutes')->numeric()->default(0)->suffix('min'),
            Forms\Components\TextInput::make('order')->numeric()->default(0)->required(),
            Forms\Components\Toggle::make('is_published')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')->sortable()->label('#'),
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Durée (min)'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Publié'),
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
