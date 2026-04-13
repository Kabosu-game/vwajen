<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Video;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Contenu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('title')->required(),
                \Filament\Forms\Components\Textarea::make('description'),
                \Filament\Forms\Components\TextInput::make('video_url')->required(),
                \Filament\Forms\Components\Select::make('status')->options([
                    'processing' => 'Traitement',
                    'published' => 'Publié',
                    'rejected' => 'Rejeté',
                    'deleted' => 'Supprimé',
                ])->required(),
                \Filament\Forms\Components\Select::make('content_type')->options([
                    'citoyen' => 'Citoyen',
                    'solution' => 'Solution',
                    'terrain' => 'Terrain',
                    'education' => 'Education',
                    'autre' => 'Autre',
                ])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('content_type')->badge(),
                Tables\Columns\TextColumn::make('views_count')->label('Vues'),
                Tables\Columns\TextColumn::make('likes_count')->label('Likes'),
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
