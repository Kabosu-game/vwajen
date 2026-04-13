<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Modération';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('reporter_id')->numeric()->required(),
                \Filament\Forms\Components\TextInput::make('reportable_type')->required(),
                \Filament\Forms\Components\TextInput::make('reportable_id')->numeric()->required(),
                \Filament\Forms\Components\Select::make('reason')->options([
                    'violence' => 'Violence',
                    'desinformation' => 'Désinformation',
                    'haine' => 'Haine',
                    'spam' => 'Spam',
                    'contenu_inapproprie' => 'Inapproprié',
                    'harcelement' => 'Harcèlement',
                    'autre' => 'Autre',
                ])->required(),
                \Filament\Forms\Components\Select::make('status')->options([
                    'pending' => 'En attente',
                    'reviewed' => 'Revu',
                    'resolved' => 'Résolu',
                    'dismissed' => 'Rejeté',
                ])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('reason')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('reportable_type'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
