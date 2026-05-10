<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationGroup = 'Contenu citoyen';
    protected static ?string $navigationLabel = 'Projets Citoyens';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(5)->columnSpanFull(),
            Forms\Components\TextInput::make('cover_url')->url()->columnSpanFull(),
            Forms\Components\Select::make('category')
                ->options([
                    'social' => 'Social', 'economique' => 'Économique',
                    'educatif' => 'Éducatif', 'environnement' => 'Environnement',
                    'sante' => 'Santé', 'technologie' => 'Technologie', 'autre' => 'Autre',
                ])->columnSpan(1),
            Forms\Components\Select::make('status')
                ->options(['draft' => 'Brouillon', 'published' => 'Publié', 'completed' => 'Terminé', 'abandoned' => 'Abandonné'])
                ->columnSpan(1),
            Forms\Components\Toggle::make('is_published')->label('Publié')->columnSpan(1),
            Forms\Components\Toggle::make('is_featured')->label('En vedette')->columnSpan(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('creator.name')->label('Créateur'),
                Tables\Columns\BadgeColumn::make('category'),
                Tables\Columns\TextColumn::make('supports_count')->label('Soutiens')->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
