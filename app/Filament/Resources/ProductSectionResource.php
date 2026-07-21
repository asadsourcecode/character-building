<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductSectionResource\Pages;
use App\Models\ProductSection;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class ProductSectionResource extends Resource
{
    protected static ?string $model = ProductSection::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                SchemaComponents\Group::make()
                    ->schema([

                        // ── Heading Content ───────────────────────────────
                        SchemaComponents\Section::make('Section Heading')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('subtitle')
                                    ->label('Subtitle')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->helperText('Shown as the description paragraph in the section heading.'),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Section Image')
                                    ->disk('public')
                                    ->directory('sections')
                                    ->visibility('public')
                                    ->image()
                                    ->columnSpanFull()
                                    ->helperText('Main image displayed on the left of the section heading.'),
                            ])
                            ->columns(2)
                            ->compact(),

                        // ── Pricing ───────────────────────────────────────
                        SchemaComponents\Section::make('Pricing')
                            ->schema([
                                SchemaComponents\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Original Price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->helperText('Shown with strikethrough when sale price is set.'),

                                        Forms\Components\TextInput::make('sale_price')
                                            ->label('Sale Price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->helperText('Must be lower than original price to show as discounted.'),

                                        Forms\Components\TextInput::make('ebook_price')
                                            ->label('eBook Price')
                                            ->numeric()
                                            ->prefix('$'),

                                        Forms\Components\TextInput::make('hardcopy_price')
                                            ->label('Hardcopy Price')
                                            ->numeric()
                                            ->prefix('$'),
                                    ]),
                            ])
                            ->compact(),

                        // ── Button ────────────────────────────────────────
                        SchemaComponents\Section::make('Button')
                            ->schema([
                                Forms\Components\TextInput::make('buy_btn_text')
                                    ->label('Button Text')
                                    ->placeholder('Buy Now')
                                    ->default('Buy Now')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('btn_url')
                                    ->label('Button URL')
                                    ->placeholder('/product/your-slug')
                                    ->maxLength(255),
                            ])
                            ->compact(),

                        // ── Display Settings ──────────────────────────────
                        SchemaComponents\Section::make('Display Settings')
                            ->schema([
                                SchemaComponents\Grid::make(3)
                                    ->schema([
                                        Forms\Components\ColorPicker::make('bg_color')
                                            ->label('Background Color')
                                            ->default('#ecfde8'),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Sort Order')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Lower number appears first.'),

                                        Forms\Components\Toggle::make('is_available')
                                            ->label('Show on Frontend')
                                            ->default(true),

                                        Forms\Components\Select::make('layout')
                                            ->label('Layout Style')
                                            ->options([
                                                'rows' => 'Rows — package heading + items listed below',
                                                'grid' => 'Grid — 3-column product cards',
                                            ])
                                            ->default('rows')
                                            ->required()
                                            ->helperText('Choose how products in this section are displayed.'),
                                    ]),
                            ])
                            ->compact(),

                    ])
                    ->extraAttributes(['class' => 'max-w-4xl mx-auto']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->width(60)
                    ->height(60),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Sale')
                    ->money('USD'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products'),

                Tables\Columns\TextColumn::make('is_published')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->is_published ? 'Published' : 'Draft')
                    ->color(fn (string $state) => $state === 'Published' ? 'success' : 'gray'),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Publish'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index'  => Pages\ListProductSections::route('/'),
            'create' => Pages\CreateProductSection::route('/create'),
            'edit'   => Pages\EditProductSection::route('/{record}/edit'),
        ];
    }
}
