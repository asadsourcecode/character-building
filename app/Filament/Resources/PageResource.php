<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    // Each template key maps to a blade file in resources/views/pages/
    public static array $templates = [
        'home'         => 'Home Page',
        'default'      => 'Default (title + rich content)',
        'homeschooling'=> 'Homeschooling',
        'counselling'  => 'Counselling & Therapy',
        'teachers'     => "Teacher's Training",
        'online'       => 'Online Learning',
        'about'        => 'About Us',
        'methodology'  => 'Methodology',
        'new-subject'  => 'New Subject',
    ];

    public static function form(Schema $form): Schema
    {
        return $form->schema([

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Page Title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Forms\Components\Select::make('template')
                    ->options(self::$templates)
                    ->default('default')
                    ->required()
                    ->live()
                    ->helperText('Determines the frontend layout used'),
            ]),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->helperText('URL slug — homepage uses "home", others use "/pages/{slug}"'),

            // ── Common fields (all pages) ────────────────────────────────────
            Forms\Components\Section::make('Main Content')->schema([
                Forms\Components\Textarea::make('short_description')
                    ->label('Short Description / Intro')
                    ->rows(3)
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => !in_array($get('template'), ['methodology', 'new-subject'])),

                Forms\Components\RichEditor::make('content')
                    ->label('Body Content (Rich Text)')
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => !in_array($get('template'), ['home', 'methodology', 'new-subject'])),

                Forms\Components\FileUpload::make('featured_image')
                    ->label('Featured Image')
                    ->image()
                    ->directory('pages')
                    ->visible(fn (Get $get) => !in_array($get('template'), ['methodology', 'new-subject'])),

                Forms\Components\FileUpload::make('banner_image')
                    ->label('Banner / Hero Image')
                    ->image()
                    ->directory('pages')
                    ->visible(fn (Get $get) => !in_array($get('template'), ['methodology', 'new-subject'])),
            ])->columns(2),

            // ── HOME PAGE meta fields ────────────────────────────────────────
            Forms\Components\Section::make('Page Settings')
                ->schema([
                    Forms\Components\TextInput::make('meta.page_title')
                        ->label('Browser Tab Title')
                        ->placeholder('ICE | Integrated Character Education')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('template') === 'home'),

            Forms\Components\Section::make('Hero Section')
                ->description('Top banner content of the homepage')
                ->schema([
                    Forms\Components\TextInput::make('meta.hero_heading')
                        ->label('Hero Heading')
                        ->placeholder('EMPOWERING'),
                    Forms\Components\TextInput::make('meta.start_journey_text')
                        ->label('Start Journey Text')
                        ->placeholder('START THE JOURNEY'),
                    Forms\Components\TextInput::make('meta.hero_subtext')
                        ->label('Hero Subtext')
                        ->placeholder('Young Minds Through Character Education')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.hero_description')
                        ->label('Hero Description Paragraph')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.hadith_text')
                        ->label('Hadith / Intro Line Below Bismillah')
                        ->placeholder('In The Name Of Allah The Merciful, The Compassionate')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.hero_strip_text')
                        ->label('Strip Text Below Banner')
                        ->rows(2)
                        ->placeholder("Ready To Transform Your Child's Character...")
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('template') === 'home'),

            Forms\Components\Section::make('Vision & Mission')
                ->schema([
                    Forms\Components\TextInput::make('meta.vision_heading')
                        ->label('Vision Heading')
                        ->placeholder('Vision'),
                    Forms\Components\TextInput::make('meta.mission_text')
                        ->label('Mission Quote Text')
                        ->placeholder('Do to others as you would have them do to you!'),
                    Forms\Components\Textarea::make('meta.vision_text')
                        ->label('Vision Statement Paragraph')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('template') === 'home'),

            Forms\Components\Section::make('Moral / Social / Emotional Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.moral_heading')
                        ->label('Section Heading')
                        ->placeholder('Moral, Social & Emotional'),
                    Forms\Components\TextInput::make('meta.moral_subheading')
                        ->label('Section Subheading')
                        ->placeholder('EDUCATION'),
                    Forms\Components\Textarea::make('meta.moral_description')
                        ->label('Section Paragraph')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('template') === 'home'),

            Forms\Components\Section::make('Character Building Course Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.course_heading')
                        ->label('Heading Line 1')
                        ->placeholder('Check Out Our'),
                    Forms\Components\TextInput::make('meta.course_subheading')
                        ->label('Heading Line 2')
                        ->placeholder('Character Building Course'),
                    Forms\Components\Textarea::make('meta.course_description')
                        ->label('Description Paragraph')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.course_purchase_text')
                        ->label('Purchase Prompt Text')
                        ->placeholder('For purchase of books, choose your level :')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.freesample_heading')
                        ->label('Free Sample Heading')
                        ->placeholder('For free sample lessons, click button:')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('template') === 'home'),

            Forms\Components\Section::make('Video Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.video_heading')
                        ->label('Section Heading')
                        ->placeholder('Video Presentation'),
                    Forms\Components\TextInput::make('meta.video_url_urdu')
                        ->label('Urdu Video URL')
                        ->placeholder('https://youtu.be/...')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.video_url_english_short')
                        ->label('English Short Video URL')
                        ->placeholder('https://youtu.be/...'),
                    Forms\Components\TextInput::make('meta.video_url_english_intro')
                        ->label('English Introduction Video URL')
                        ->placeholder('https://youtu.be/...'),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('template') === 'home'),

            Forms\Components\Section::make('Partners Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.partners_heading')
                        ->label('Partners Heading')
                        ->placeholder('We Collaborate With a'),
                    Forms\Components\TextInput::make('meta.partners_subheading')
                        ->label('Partners Subheading')
                        ->placeholder('Number of Leading Schools and Institutes'),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('template') === 'home'),

            Forms\Components\Section::make('Promo Banner')
                ->schema([
                    Forms\Components\FileUpload::make('meta.promo_banner')
                        ->label('Promo Banner Image')
                        ->image()
                        ->directory('pages/home')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('template') === 'home'),

            // ── METHODOLOGY PAGE meta fields ────────────────────────────────
            Forms\Components\Section::make('Background Images')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_methodology')
                        ->label('Top Section Background (bg-methodology)')
                        ->image()
                        ->directory('pages/methodology')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('meta.metha_1_image')
                        ->label('Hypotheses Section Background (metha_1)')
                        ->image()
                        ->directory('pages/methodology')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visible(fn (Get $get) => $get('template') === 'methodology'),

            Forms\Components\Section::make('Preface Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.preface_heading')
                        ->label('Preface Heading')
                        ->placeholder('PREFACE AND THE THREE HYPOTHESES OF THE COURSE')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.preface_subtext')
                        ->label('Preface Subtext')
                        ->rows(2)
                        ->placeholder('The approach of this Character Education School Course can be summarised through the following aims/hypotheses:')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visible(fn (Get $get) => $get('template') === 'methodology'),

            Forms\Components\Section::make('Three Hypotheses')
                ->schema([
                    Forms\Components\RichEditor::make('meta.hypothesis_1')
                        ->label('Hypothesis 1')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('meta.hypothesis_2')
                        ->label('Hypothesis 2')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('meta.hypothesis_3')
                        ->label('Hypothesis 3')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.hypotheses_closing')
                        ->label('Closing Paragraph (below 3 hypotheses)')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visible(fn (Get $get) => $get('template') === 'methodology'),

            Forms\Components\Section::make('Middle Banner Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.image_1')
                        ->label('Middle Banner Image (wide horizontal image)')
                        ->image()
                        ->directory('pages/methodology')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visible(fn (Get $get) => $get('template') === 'methodology'),

            Forms\Components\Section::make('Main Objective')
                ->schema([
                    Forms\Components\Textarea::make('meta.objective_text')
                        ->label('Objective Main Text')
                        ->rows(3)
                        ->placeholder('The main objective of ICE Publishers is to provide...')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.objective_highlight')
                        ->label('Objective Highlighted Text (shown in bold)')
                        ->rows(3)
                        ->placeholder('This aim is measured by whether and to what extent...')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visible(fn (Get $get) => $get('template') === 'methodology'),

            Forms\Components\Section::make('Bottom Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.image_2')
                        ->label('Bottom Image')
                        ->image()
                        ->directory('pages/methodology')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->visible(fn (Get $get) => $get('template') === 'methodology'),

            // ── NEW SUBJECT PAGE fields ──────────────────────────────────────
            // Section 1: Launching Heading (bold text below the title box)
            Forms\Components\Section::make('Launching Heading')
                ->description('Bold heading shown below the title box. Use a new line for the line break.')
                ->schema([
                    Forms\Components\Textarea::make('meta.launching_heading')
                        ->label('Launching Heading Text')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('LAUNCHING "CHARACTER EDUCATION" AS A SEPARATE SUBJECT FOR MUSLIM INSTITUTIONS, ISLAMIC' . "\n" . 'COMMUNITY PROGRAMMES, HOME-SCHOOLING AND ONLINE-TEACHING'),
                ])
                ->visible(fn (Get $get) => $get('template') === 'new-subject'),

            // Section 2: Four intro paragraphs (shown above the building image)
            Forms\Components\Section::make('Introduction Paragraphs')
                ->description('Four paragraphs shown above the building image')
                ->schema([
                    Forms\Components\Textarea::make('meta.para_1')
                        ->label('Paragraph 1')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('The programme has been introduced to convey ethics to children pedagogically and effectively...'),
                    Forms\Components\Textarea::make('meta.para_2')
                        ->label('Paragraph 2')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('The curriculum aims to provide the student/pupil with a comprehensive ethical programme...'),
                    Forms\Components\Textarea::make('meta.para_3')
                        ->label('Paragraph 3')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Character Building is a complementary subject taught alongside traditional courses...'),
                    Forms\Components\Textarea::make('meta.para_4')
                        ->label('Paragraph 4')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Almost no homework and no grading but lots of stories, interactive communication...'),
                ])
                ->visible(fn (Get $get) => $get('template') === 'new-subject'),

            // Section 3: Building image (shown between intro paragraphs and bullet list)
            Forms\Components\Section::make('Building Image')
                ->description('Image shown below the 4 intro paragraphs')
                ->schema([
                    Forms\Components\FileUpload::make('meta.building_image')
                        ->label('Building Image')
                        ->image()
                        ->directory('pages/new-subject')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('template') === 'new-subject'),

            // Section 4: Bullet list section
            Forms\Components\Section::make('Bullet List Section')
                ->description('Text before the bullet list, then the bullet items themselves')
                ->schema([
                    Forms\Components\Textarea::make('meta.approach_text')
                        ->label('Text Before Bullet List')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('The approach of this Theme, which also is a turning point in the educational traditions of religious education for Muslim communities all over the world. All types of:'),
                    Forms\Components\Textarea::make('meta.bullet_items')
                        ->label('Bullet Items (one item per line)')
                        ->rows(18)
                        ->columnSpanFull()
                        ->helperText('Each line = one bullet point')
                        ->placeholder("Stories\nReal events\nPictures\nCreative and involving pedagogical activities..."),
                ])
                ->visible(fn (Get $get) => $get('template') === 'new-subject'),

            // Section 5: Four closing paragraphs (after bullet list, before girls image)
            Forms\Components\Section::make('Closing Paragraphs')
                ->description('Four paragraphs shown after the bullet list')
                ->schema([
                    Forms\Components\Textarea::make('meta.quote_text')
                        ->label('Quote Paragraph (shown in bold italic)')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('"What is the use of worldly education if our children do not develop into good human beings?...'),
                    Forms\Components\Textarea::make('meta.educationists_text')
                        ->label('Educationists Paragraph')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Educationists have labelled this "as one of the first serious international attempts...'),
                    Forms\Components\Textarea::make('meta.programme_aims_text')
                        ->label('Programme Aims Paragraph')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('The programme aims to motivate and inspire teachers, educators and parents...'),
                    Forms\Components\Textarea::make('meta.closing_prayer_text')
                        ->label('Closing Prayer Paragraph')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('May this effort, by the grace of Allah, begin a trend of teaching ethics...'),
                ])
                ->visible(fn (Get $get) => $get('template') === 'new-subject'),

            // Section 6: Girls image (bottom of page)
            Forms\Components\Section::make('Girls Image')
                ->description('Image shown at the very bottom of the page')
                ->schema([
                    Forms\Components\FileUpload::make('meta.girls_image')
                        ->label('Girls Image')
                        ->image()
                        ->directory('pages/new-subject')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('template') === 'new-subject'),

            // ── SEO ─────────────────────────────────────────────────────────
            Forms\Components\Section::make('SEO')->schema([
                Forms\Components\TextInput::make('meta_title')->maxLength(255),
                Forms\Components\Textarea::make('meta_description')->columnSpanFull()->rows(2),
            ])->collapsed(),

            Forms\Components\Toggle::make('status')->default(true)->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('template')->badge()->color('info'),
                Tables\Columns\ImageColumn::make('featured_image'),
                Tables\Columns\IconColumn::make('status')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit'   => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
