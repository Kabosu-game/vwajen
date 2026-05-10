<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Modération';

    protected static ?string $navigationLabel = 'Commentaires';

    protected static ?string $modelLabel = 'commentaire';

    protected static ?string $pluralModelLabel = 'commentaires';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('commentable_type')
                    ->label('Type polymorphique (classe)')
                    ->required(),
                Forms\Components\TextInput::make('commentable_id')
                    ->label('ID contenu')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('parent_id')
                    ->label('Commentaire parent (ID)')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->rows(5),
                Forms\Components\Select::make('status')
                    ->options([
                        'visible' => 'Visible',
                        'hidden' => 'Masqué',
                        'reported' => 'Signalé',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Auteur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('commentable_type')
                    ->label('Cible')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : ''),
                Tables\Columns\TextColumn::make('commentable_id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->wrap()
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('likes_count')->label('Likes'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'visible' => 'Visible',
                        'hidden' => 'Masqué',
                        'reported' => 'Signalé',
                    ]),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
