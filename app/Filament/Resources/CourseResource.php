<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\RelationManagers\LessonsRelationManager;
use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Éducation';

    protected static ?string $navigationLabel = 'Cours';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->label('Catégorie')
                ->relationship(
                    name: 'category',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($q) => $q->where('type', 'education')->where('is_active', true)->orderBy('order')->orderBy('name')
                )
                ->preload()
                ->searchable(),
            Forms\Components\Select::make('created_by')
                ->label('Créateur')
                ->relationship('creator', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('title')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(static fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                ->columnSpanFull(),

            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->columnSpanFull(),

            Forms\Components\Textarea::make('description')->rows(6)->required()->columnSpanFull(),

            Forms\Components\FileUpload::make('thumbnail')
                ->image()
                ->disk('public')
                ->directory('courses/thumbnails')
                ->columnSpanFull(),

            Forms\Components\Select::make('level')
                ->options([
                    'debutant' => 'Débutant',
                    'intermediaire' => 'Intermédiaire',
                    'avance' => 'Avancé',
                ])
                ->required(),
            Forms\Components\Select::make('type')
                ->options([
                    'leadership' => 'Leadership',
                    'citoyennete' => 'Citoyenneté',
                    'politique' => 'Politique',
                    'organisation' => 'Organisation',
                    'autre' => 'Autre',
                ])
                ->required(),

            Forms\Components\TextInput::make('duration_minutes')
                ->numeric()
                ->suffix('min')
                ->placeholder('Durée indicative du cours'),
            Forms\Components\TextInput::make('points_reward')
                ->numeric()
                ->default(0)
                ->label('Points de récompense'),

            Forms\Components\Toggle::make('is_published')->label('Publié'),
            Forms\Components\Toggle::make('is_featured')->label('À la une'),
            Forms\Components\Toggle::make('is_free')->label('Gratuit'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('category.name')->label('Catégorie')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('level')->badge(),
                Tables\Columns\TextColumn::make('lessons_count')->counts('lessons')->label('Leçons'),
                Tables\Columns\TextColumn::make('enrollments_count')->label('Inscriptions')->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Publié'),
                Tables\Filters\SelectFilter::make('type'),
            ])
            ->defaultSort('title')
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
            LessonsRelationManager::class,
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
