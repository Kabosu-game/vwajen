<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CivicActionResource\Pages;
use App\Models\CivicAction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CivicActionResource extends Resource
{
    protected static ?string $model = CivicAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Mobilisation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('title')->required(),
                \Filament\Forms\Components\TextInput::make('slug')->required(),
                \Filament\Forms\Components\Textarea::make('description')->required(),
                \Filament\Forms\Components\TextInput::make('location')->required(),
                \Filament\Forms\Components\Select::make('status')->options([
                    'planned' => 'Planifiée',
                    'active' => 'Active',
                    'completed' => 'Terminée',
                    'cancelled' => 'Annulée',
                ])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('action_date')->dateTime(),
                Tables\Columns\TextColumn::make('participants_count')->label('Participants'),
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
            'index' => Pages\ListCivicActions::route('/'),
            'create' => Pages\CreateCivicAction::route('/create'),
            'edit' => Pages\EditCivicAction::route('/{record}/edit'),
        ];
    }
}
