<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorruptionReportResource\Pages;
use App\Models\CorruptionReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CorruptionReportResource extends Resource
{
    protected static ?string $model = CorruptionReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationGroup = 'Coopération & intégrité';
    protected static ?string $navigationLabel = 'Dénonciations Corruption';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = CorruptionReport::whereIn('status', ['pending', 'under_review'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category')
                ->options([
                    'administration_publique' => 'Administration Publique',
                    'niveau_local_communal'   => 'Niveau Local / Communal',
                    'projets_institutions'    => 'Projets / Institutions',
                    'police_justice'          => 'Police / Justice',
                    'education'               => 'Éducation',
                    'sante'                   => 'Santé',
                    'autre'                   => 'Autre',
                ])
                ->required()
                ->columnSpan(1),

            Forms\Components\Select::make('status')
                ->options([
                    'pending'      => 'En attente',
                    'under_review' => 'En cours d\'examen',
                    'verified'     => 'Vérifié',
                    'dismissed'    => 'Rejeté',
                ])
                ->required()
                ->columnSpan(1),

            Forms\Components\TextInput::make('title')
                ->required()
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->rows(5)
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_verified')
                ->label('Dénonciation vérifiée'),

            Forms\Components\Textarea::make('moderator_note')
                ->label('Note du modérateur')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Catégorie')
                    ->colors([
                        'danger'  => 'administration_publique',
                        'warning' => 'niveau_local_communal',
                        'primary' => 'projets_institutions',
                        'success' => 'education',
                    ]),
                Tables\Columns\TextColumn::make('title')->limit(50)->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning'   => 'under_review',
                        'success'   => 'verified',
                        'danger'    => 'dismissed',
                    ]),
                Tables\Columns\IconColumn::make('is_verified')->boolean()->label('Vérifié'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'under_review' => 'En révision',
                        'verified' => 'Vérifiés',
                        'dismissed' => 'Rejetés',
                    ]),
                Tables\Filters\SelectFilter::make('category'),
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
            'index'  => Pages\ListCorruptionReports::route('/'),
            'edit'   => Pages\EditCorruptionReport::route('/{record}/edit'),
        ];
    }
}
