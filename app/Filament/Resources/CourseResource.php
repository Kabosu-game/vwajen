<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Education';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('title')->required(),
                \Filament\Forms\Components\TextInput::make('slug')->required(),
                \Filament\Forms\Components\Textarea::make('description')->required(),
                \Filament\Forms\Components\Select::make('level')->options([
                    'debutant' => 'Débutant',
                    'intermediaire' => 'Intermédiaire',
                    'avance' => 'Avancé',
                ])->required(),
                \Filament\Forms\Components\Select::make('type')->options([
                    'leadership' => 'Leadership',
                    'citoyennete' => 'Citoyenneté',
                    'politique' => 'Politique',
                    'organisation' => 'Organisation',
                    'autre' => 'Autre',
                ])->required(),
                \Filament\Forms\Components\Toggle::make('is_published'),
                \Filament\Forms\Components\Toggle::make('is_featured'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('level')->badge(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('enrollments_count')->label('Inscriptions'),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
