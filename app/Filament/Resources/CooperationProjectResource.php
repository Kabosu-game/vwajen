<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CooperationProjectResource\Pages;
use App\Models\CooperationProject;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CooperationProjectResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('author');
    }

    protected static ?string $model = CooperationProject::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Coopération & intégrité';
    protected static ?string $navigationLabel = 'Projets de Coopération';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('listing_type')
                ->label('Type d’annonce')
                ->options([
                    'collaboration' => 'Projet / collaboration',
                    'job' => 'Offre d’emploi',
                    'announcement' => 'Annonce générale',
                    'exchange' => 'Échange international',
                ])
                ->required()
                ->default('collaboration')
                ->columnSpan(1),
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('cover_url')->url()->columnSpanFull(),
            Forms\Components\TextInput::make('countries')->placeholder('Ex: Sénégal, Côte d\'Ivoire')->columnSpan(2),
            Forms\Components\Select::make('sector')
                ->options([
                    'business' => 'Business', 'education' => 'Éducation',
                    'agriculture' => 'Agriculture', 'sante' => 'Santé',
                    'technologie' => 'Technologie', 'culture' => 'Culture', 'autre' => 'Autre',
                ])->columnSpan(1),
            Forms\Components\TextInput::make('organization')->columnSpan(1),
            Forms\Components\TextInput::make('contact_email')->email()->columnSpan(2),
            Forms\Components\Toggle::make('is_published')->label('Publié (visible app)')->columnSpan(1),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
                Tables\Columns\BadgeColumn::make('listing_type')
                    ->formatStateUsing(fn (?string $s) => match ($s) {
                        'collaboration' => 'Collaboration',
                        'job' => 'Emploi',
                        'announcement' => 'Annonce',
                        'exchange' => 'Échange',
                        default => $s ?? '—',
                    }),
                Tables\Columns\TextColumn::make('author.name')->label('Auteur')->default('—'),
                Tables\Columns\TextColumn::make('countries'),
                Tables\Columns\BadgeColumn::make('sector'),
                Tables\Columns\TextColumn::make('organization'),
                Tables\Columns\TextColumn::make('interests_count')->label('Intérêts')->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Publié'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index'  => Pages\ListCooperationProjects::route('/'),
            'create' => Pages\CreateCooperationProject::route('/create'),
            'edit'   => Pages\EditCooperationProject::route('/{record}/edit'),
        ];
    }
}
