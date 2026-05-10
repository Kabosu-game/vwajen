<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MuseumCategoryResource\Pages;
use App\Models\MuseumCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MuseumCategoryResource extends Resource
{
    protected static ?string $model = MuseumCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Musée des révolutionnaires';

    protected static ?string $navigationLabel = 'Catégories du musée';

    protected static ?string $modelLabel = 'Catégorie';

    protected static ?string $pluralModelLabel = 'Catégories';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nom')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(static fn ($state, Forms\Set $set) => $set('slug', Str::slug((string) $state)))
                ->columnSpan(2),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->columnSpan(2),

            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required()
                ->columnSpan(1),

            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->columnSpan(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->badge()->searchable(),
                Tables\Columns\TextColumn::make('museum_entries_count')
                    ->label('Révolutionnaires')
                    ->counts('museumEntries'),
                Tables\Columns\TextColumn::make('sort_order')->sortable()->label('Ordre'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMuseumCategories::route('/'),
            'create' => Pages\CreateMuseumCategory::route('/create'),
            'edit' => Pages\EditMuseumCategory::route('/{record}/edit'),
        ];
    }
}
