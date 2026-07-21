<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\ClassRoom;
use App\Models\Product;
use App\Models\ProductSection;
use App\Models\Subject;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static \UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                SchemaComponents\Group::make()
                    ->schema([

                        // ── Basic Info ────────────────────────────────────
                        SchemaComponents\Section::make('Basic Info')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($set, $state) {
                                        if (filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Auto-filled when you type a title. You can edit it manually if needed.'),

                                Forms\Components\Select::make('section_id')
                                    ->label('Product Section')
                                    ->options(ProductSection::orderBy('sort_order')->pluck('title', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->helperText('Assign this product to a section on the pricing page.'),

                                Forms\Components\Select::make('class_filter')
                                    ->label('Class')
                                    ->options(fn () => ClassRoom::pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $record) {
                                        if ($record?->subject?->class_id) {
                                            $component->state($record->subject->class_id);
                                        }
                                    })
                                    ->afterStateUpdated(fn (Set $set) => $set('subject_id', null))
                                    ->helperText('Pick a class to filter the subject list below.'),

                                Forms\Components\Select::make('subject_id')
                                    ->label('Subject')
                                    ->relationship('subject', 'name')
                                    ->options(fn (Get $get) => $get('class_filter')
                                        ? Subject::where('class_id', $get('class_filter'))->pluck('name', 'id')
                                        : [])
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (Get $get) => ! $get('class_filter'))
                                    ->helperText('Which subject this book belongs to.'),

                                Forms\Components\TextInput::make('subtitle')
                                    ->label('Subtitle')
                                    ->maxLength(255)
                                    ->placeholder('e.g. eBook 1 Year Subscription')
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('description')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->compact(),

                        // ── Images ────────────────────────────────────────
                        SchemaComponents\Section::make('Images')
                            ->schema([
                                Forms\Components\FileUpload::make('images')
                                    ->label('Product Images')
                                    ->multiple()
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->image()
                                    ->maxFiles(10)
                                    ->reorderable()
                                    ->helperText('Main product images shown on the frontend.'),

                                Forms\Components\FileUpload::make('featured_image')
                                    ->label('Featured Image (legacy)')
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->image()
                                    ->helperText('Single featured image. Used as fallback if no images are set above.'),
                            ])
                            ->compact(),

                        // ── Book PDF ──────────────────────────────────────
                        SchemaComponents\Section::make('Book PDF')
                            ->schema([
                                Forms\Components\FileUpload::make('pdf_path')
                                    ->label('Book PDF')
                                    ->disk('local')
                                    ->directory('product-pdfs')
                                    ->visibility('private')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(51200)
                                    ->helperText('Uploading a PDF splits it into page images in the background (via the queue). Re-uploading replaces the existing pages.'),

                                Forms\Components\Placeholder::make('pdf_conversion_status')
                                    ->label('Conversion Status')
                                    ->content(fn ($record) => $record
                                        ? sprintf('%s (%d pages)', $record->pdf_conversion_status ?? 'not uploaded', $record->pdf_page_count)
                                        : '—')
                                    ->visible(fn ($record) => $record !== null),
                            ])
                            ->compact(),

                        // ── Pricing ───────────────────────────────────────
                        SchemaComponents\Section::make('Pricing')
                            ->schema([
                                SchemaComponents\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Original Price')
                                            ->numeric()
                                            ->prefix('$'),

                                        Forms\Components\TextInput::make('sale_price')
                                            ->label('Sale Price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->helperText('Set lower than original price to show as discounted.'),

                                        Forms\Components\TextInput::make('compare_at_price')
                                            ->label('Compare At Price')
                                            ->numeric()
                                            ->prefix('$'),

                                        Forms\Components\TextInput::make('ebook_price')
                                            ->label('eBook Price (1 Year Subscription)')
                                            ->numeric()
                                            ->prefix('$'),

                                        Forms\Components\TextInput::make('hardcopy_price')
                                            ->label('Hardcopy Price')
                                            ->numeric()
                                            ->prefix('$'),

                                        Forms\Components\TextInput::make('pages')
                                            ->label('Pages')
                                            ->numeric(),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->numeric()
                                            ->prefix('$'),

                                        Forms\Components\Select::make('unit')
                                            ->options([
                                                'piece' => 'Piece',
                                                'kg'    => 'Kilogram',
                                                'g'     => 'Gram',
                                                'lb'    => 'Pound',
                                                'l'     => 'Liter',
                                                'ml'    => 'Milliliter',
                                                'm'     => 'Meter',
                                                'cm'    => 'Centimeter',
                                            ])
                                            ->default('piece'),

                                        Forms\Components\TextInput::make('total_amount')
                                            ->label('Total Amount')
                                            ->numeric()
                                            ->prefix('$'),
                                    ]),
                            ])
                            ->compact(),

                        // ── Inventory ─────────────────────────────────────
                        SchemaComponents\Section::make('Inventory')
                            ->schema([
                                SchemaComponents\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('stock')
                                            ->label('Quantity in Stock')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Set to 0 and enable "Sell when out of stock" to show Buy button while restocking.'),

                                        Forms\Components\Toggle::make('allow_oversell')
                                            ->label('Sell when out of stock (shows Buy button even if stock = 0)')
                                            ->default(true),

                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('barcode')
                                            ->label('Barcode (ISBN, UPC, GTIN…)')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->compact(),

                        // ── Shipping ──────────────────────────────────────
                        SchemaComponents\Section::make('Shipping')
                            ->schema([
                                SchemaComponents\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Toggle::make('requires_shipping')
                                            ->label('Requires Shipping')
                                            ->default(true),

                                        Forms\Components\TextInput::make('weight')
                                            ->numeric(),

                                        Forms\Components\Select::make('weight_unit')
                                            ->options(['kg' => 'Kilograms', 'g' => 'Grams', 'lb' => 'Pounds', 'oz' => 'Ounces'])
                                            ->default('kg'),

                                        Forms\Components\TextInput::make('country_of_origin')
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('hs_code')
                                            ->label('HS Code')
                                            ->maxLength(50),
                                    ]),
                            ])
                            ->compact()
                            ->collapsed()
                            ->collapsible(),

                        // ── Variants ──────────────────────────────────────
                        SchemaComponents\Section::make('Variants')
                            ->schema([
                                Forms\Components\Repeater::make('variants')
                                    ->schema([
                                        Forms\Components\TextInput::make('option_name')
                                            ->label('Option Name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('option_values')
                                            ->label('Values (comma-separated)')
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ])
                            ->compact()
                            ->collapsed()
                            ->collapsible(),

                        // ── Buy Button ────────────────────────────────────
                        SchemaComponents\Section::make('Buy Button')
                            ->schema([
                                Forms\Components\TextInput::make('btn_label')
                                    ->label('Button Text')
                                    ->placeholder('Buy Now')
                                    ->maxLength(100)
                                    ->helperText('Leave blank to use the section default (e.g. "Buy Now"). Set to any text to override for this product only (e.g. "Contact Us", "Pre-order").'),
                            ])
                            ->compact(),

                        // ── SEO & Status ──────────────────────────────────
                        SchemaComponents\Section::make('SEO & Status')
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label('Active (visible on frontend)')
                                    ->default(true)
                                    ->required(),

                                Forms\Components\TextInput::make('meta_title')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('meta_description')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('sample_pdf')
                                    ->label('Sample PDF URL')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->compact(),

                    ])
                    ->extraAttributes(['class' => 'max-w-5xl mx-auto']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Image')
                    ->disk('public')
                    ->getStateUsing(fn($record) => collect($record->images)->first())
                    ->width(60)
                    ->height(60),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('section.title')
                    ->label('Section')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('subject.classRoom.name')
                    ->label('Class')
                    ->placeholder('—'),


                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Sale')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('status')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id')
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
