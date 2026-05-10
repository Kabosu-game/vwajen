<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MuseumEntryResource\Pages;
use App\Models\MuseumEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MuseumEntryResource extends Resource
{
    protected static ?string $model = MuseumEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Musée des révolutionnaires';

    protected static ?string $navigationLabel = 'Fiches révolutionnaires';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('museum_category_id')
                ->label('Catégorie')
                ->relationship(
                    name: 'category',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($query) => $query->where('is_active', true)->ordered()
                )
                ->nullable()
                ->preload()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(static fn ($state, Forms\Set $set) => $set('slug', Str::slug((string) $state)))
                ->columnSpan(2),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->columnSpan(2),

            Forms\Components\FileUpload::make('portrait_url')
                ->label('Photo (portrait)')
                ->image()
                ->disk('public')
                ->directory('museum/portraits')
                ->imageEditor()
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label('Biographie / description')
                ->rows(8)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('gallery')
                ->label('Galerie photos')
                ->multiple()
                ->reorderable()
                ->image()
                ->disk('public')
                ->directory('museum/gallery')
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_featured')->label('En vedette')->columnSpan(1),
            Forms\Components\Toggle::make('is_published')->label('Publié')->columnSpan(1),
        ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('portrait_url')
                    ->label('Portrait')
                    ->disk('public')
                    ->height(48)
                    ->square(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Catégorie')->searchable()->badge(),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Vedette'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Publié'),
                Tables\Columns\TextColumn::make('views_count')->sortable()->label('Vues'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('museum_category_id')
                    ->relationship('category', 'name')
                    ->label('Catégorie'),
                Tables\Filters\TernaryFilter::make('is_published')->label('Publié'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('En vedette'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMuseumEntries::route('/'),
            'create' => Pages\CreateMuseumEntry::route('/create'),
            'edit' => Pages\EditMuseumEntry::route('/{record}/edit'),
        ];
    }
}
