<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contenu citoyen';
    protected static ?string $navigationLabel = 'Publications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('text')->rows(4)->columnSpanFull(),
            Forms\Components\Select::make('type')
                ->options(['text' => 'Texte', 'image' => 'Image', 'video' => 'Vidéo'])
                ->required()->columnSpan(1),
            Forms\Components\Toggle::make('is_published')->label('Publié')->columnSpan(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Auteur')->searchable(),
                Tables\Columns\TextColumn::make('text')->limit(60)->label('Contenu'),
                Tables\Columns\BadgeColumn::make('type'),
                Tables\Columns\TextColumn::make('likes_count')->label('Likes')->sortable(),
                Tables\Columns\TextColumn::make('comments_count')->label('Commentaires')->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Publié'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\TernaryFilter::make('is_published')->label('Publié'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'edit'  => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
