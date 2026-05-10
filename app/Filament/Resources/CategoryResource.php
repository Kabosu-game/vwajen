<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Éducation';

    protected static ?string $navigationLabel = 'Catégories (cours / contenu)';

    protected static ?string $modelLabel = 'Catégorie';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(static fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                ->columnSpan(2),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->columnSpan(2),

            Forms\Components\Select::make('type')
                ->options([
                    'education' => 'Éducation (cours)',
                    'content' => 'Contenu (vidéos)',
                    'event' => 'Événements',
                    'action' => 'Actions citoyennes',
                ])
                ->required()
                ->columnSpan(2),

            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('icon')->placeholder('Icône / emoji')->columnSpan(1),
            Forms\Components\ColorPicker::make('color')->default('#000000')->columnSpan(1),
            Forms\Components\TextInput::make('order')->numeric()->default(0)->columnSpan(1),
            Forms\Components\Toggle::make('is_active')->default(true)->columnSpan(1),
        ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->badge()->searchable(),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('courses_count')->counts('courses')->label('Cours'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->defaultSort('order')
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
