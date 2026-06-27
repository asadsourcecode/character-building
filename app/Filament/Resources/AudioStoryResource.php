<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AudioStoryResource\Pages;
use App\Filament\Resources\AudioStoryResource\RelationManagers;
use App\Models\AudioStory;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AudioStoryResource extends Resource
{
    protected static ?string $model = AudioStory::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('cover_image')
                    ->image(),
                Forms\Components\TextInput::make('audio_file')
                    ->maxLength(255),
                Forms\Components\TextInput::make('audio_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('duration')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('cover_image'),
                Tables\Columns\TextColumn::make('audio_file')
                    ->searchable(),
                Tables\Columns\TextColumn::make('audio_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListAudioStories::route('/'),
            'create' => Pages\CreateAudioStory::route('/create'),
            'edit' => Pages\EditAudioStory::route('/{record}/edit'),
        ];
    }
}
