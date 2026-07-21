<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Content';

public static function form(Schema $form): Schema
    {
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('Page Title')
                ->required()
                ->live()
                ->hint(fn ($state, Get $get) => in_array($get('slug'), ['hard-copies', 'online-classes', 'feature-homeschooling', 'audio-stories', 'feature-teachers-training', 'feature-counselling-therapy', 'about', 'methodology', 'contact'])
                    ? (100 - mb_strlen($state ?? '')) . ' characters remaining'
                    : null)
                ->hintColor(fn ($state, Get $get) => in_array($get('slug'), ['hard-copies', 'online-classes', 'feature-homeschooling', 'audio-stories', 'feature-teachers-training', 'feature-counselling-therapy', 'about', 'methodology', 'contact']) && mb_strlen($state ?? '') > 100
                    ? 'danger'
                    : 'gray')
                ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                    if ($operation === 'create') {
                        $set('slug', Str::slug($state));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->live(onBlur: true)
                ->helperText('URL slug — homepage uses "home", others use "/pages/{slug}"'),

            // ── Common fields (all pages) ────────────────────────────────────
            SchemaComponents\Section::make('Main Content')->schema([
                Forms\Components\RichEditor::make('content')
                    ->label('Body Content (Rich Text)')
                    ->columnSpanFull()
                    ->live()
                    ->hint(fn ($state, $record) => $record?->content
                        ? (mb_strlen(strip_tags($record->content)) - mb_strlen(strip_tags($state ?? ''))) . ' characters remaining'
                        : null)
                    ->hintColor(fn ($state, $record) => $record?->content && mb_strlen(strip_tags($state ?? '')) > mb_strlen(strip_tags($record->content))
                        ? 'danger'
                        : 'gray')
                    ->visible(fn (Get $get) => !in_array($get('slug'), ['home', 'methodology', 'new-subject', 'ebooks', 'hard-copies'])),

                Forms\Components\FileUpload::make('featured_image')
                    ->label('Featured Image (Title Icon)')
                    ->image()
                    ->directory('pages')
                    ->helperText('For Hard Copies: shown next to the title button')
                    ->visible(fn (Get $get) => !in_array($get('slug'), ['methodology', 'new-subject', 'ebooks', 'online-classes', 'audio-stories', 'feature-teachers-training', 'feature-counselling-therapy'])),

                Forms\Components\FileUpload::make('banner_image')
                    ->label('Background Image')
                    ->image()
                    ->directory('pages')
                    ->visible(fn (Get $get) => !in_array($get('slug'), ['methodology', 'new-subject', 'ebooks', 'hard-copies'])),
            ])
            ->columns(2)
            ->columnSpanFull()
            ->visible(fn (Get $get) => !in_array($get('slug'), ['methodology', 'new-subject', 'ebooks', 'online-classes', 'audio-stories', 'feature-teachers-training', 'feature-counselling-therapy'])),

            // ── ABOUT PAGE meta fields ───────────────────────────────────────
            SchemaComponents\Section::make('Logo / Watermark Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.logo_image')
                        ->label('Bottom Logo Image')
                        ->image()
                        ->directory('pages')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'about'),

            SchemaComponents\Section::make('About the Author')
                ->schema([
                    Forms\Components\RichEditor::make('meta.author_content')
                        ->label('Author Content')
                        ->columnSpanFull()
                        ->live()
                        ->hint(fn ($state) => (1144 - mb_strlen(strip_tags(is_string($state) ? $state : ''))) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen(strip_tags(is_string($state) ? $state : '')) > 1144 ? 'danger' : 'gray'),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'about'),

            // ── HOME PAGE meta fields ────────────────────────────────────────
            SchemaComponents\Section::make('Page Settings')
                ->schema([
                    Forms\Components\TextInput::make('meta.page_title')
                        ->label('Browser Tab Title')
                        ->placeholder('ICE | Integrated Character Education')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            SchemaComponents\Section::make('Hero Section')
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
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            SchemaComponents\Section::make('Vision & Mission')
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
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            SchemaComponents\Section::make('Moral / Social / Emotional Section')
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
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            SchemaComponents\Section::make('Character Building Course Section')
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
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            SchemaComponents\Section::make('Video Section')
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
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            SchemaComponents\Section::make('Partners Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.partners_heading')
                        ->label('Partners Heading')
                        ->placeholder('We Collaborate With a'),
                    Forms\Components\TextInput::make('meta.partners_subheading')
                        ->label('Partners Subheading')
                        ->placeholder('Number of Leading Schools and Institutes'),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            SchemaComponents\Section::make('Promo Banner')
                ->schema([
                    Forms\Components\FileUpload::make('meta.promo_banner')
                        ->label('Promo Banner Image')
                        ->image()
                        ->directory('pages/home')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'home'),

            // ── METHODOLOGY PAGE meta fields ────────────────────────────────
            SchemaComponents\Section::make('Background Images')
                ->schema([
                    Forms\Components\Radio::make('meta.bg_color_mode')
                        ->label('Top Section Background')
                        ->options([
                            'default' => 'Default (background image)',
                            'custom'  => 'Custom Color',
                        ])
                        ->default('default')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\ColorPicker::make('meta.bg_color_top')
                        ->label('Pick Color')
                        ->visible(fn (Get $get) => $get('meta.bg_color_mode') === 'custom')
                        ->extraAttributes(['style' => 'max-width: 220px;']),
                    Forms\Components\FileUpload::make('meta.metha_1_image')
                        ->label('Hypotheses Section Background')
                        ->image()
                        ->directory('pages/methodology')
                        ->helperText('Use a rectangular image (no transparency, no round shapes). Recommended minimum: 1200×800px. The image will be cropped to fill the section — large images work best.')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'methodology'),

            SchemaComponents\Section::make('Preface Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.preface_heading')
                        ->label('Preface Heading')
                        ->placeholder('PREFACE AND THE THREE HYPOTHESES OF THE COURSE')
                        ->live()
                        ->hint(fn ($state) => (100 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 100 ? 'danger' : 'gray'),
                    Forms\Components\Textarea::make('meta.preface_subtext')
                        ->label('Preface Subtext')
                        ->rows(3)
                        ->placeholder('The approach of this Character Education School Course can be summarised through the following aims/hypotheses:')
                        ->live()
                        ->hint(fn ($state) => (350 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 350 ? 'danger' : 'gray'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'methodology'),

            SchemaComponents\Section::make('Hypotheses')
                ->schema([
                    Forms\Components\Repeater::make('meta.hypotheses')
                        ->label(false)
                        ->schema([
                            Forms\Components\RichEditor::make('content')
                                ->label('Content')
                                ->columnSpanFull(),
                        ])
                        ->addActionLabel('+ Add Hypothesis')
                        ->collapsible()
                        ->collapsed()
                        ->reorderableWithButtons()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.hypotheses_closing')
                        ->label('Closing Paragraph (below hypotheses)')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'methodology'),

            SchemaComponents\Section::make('Middle Banner Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.image_1')
                        ->label('Middle Banner Image')
                        ->image()
                        ->directory('pages/methodology')
                        ->helperText('If empty, defaults to the original Shopify CDN image')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'methodology'),

            SchemaComponents\Section::make('Main Objective')
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
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'methodology'),

            SchemaComponents\Section::make('Bottom Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.image_2')
                        ->label('Bottom Image')
                        ->image()
                        ->directory('pages/methodology')
                        ->helperText('If empty, defaults to the original Shopify CDN image')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'methodology'),

            // ── NEW SUBJECT PAGE fields ──────────────────────────────────────
            // Section 1: Launching Heading (bold text below the title box)
            SchemaComponents\Section::make('Launching Heading')
                ->description('Bold heading shown below the title box. Use a new line for the line break.')
                ->schema([
                    Forms\Components\Textarea::make('meta.launching_heading')
                        ->label('Launching Heading Text')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('LAUNCHING "CHARACTER EDUCATION" AS A SEPARATE SUBJECT FOR MUSLIM INSTITUTIONS, ISLAMIC' . "\n" . 'COMMUNITY PROGRAMMES, HOME-SCHOOLING AND ONLINE-TEACHING'),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'new-subject'),

            // Section 2: Four intro paragraphs (shown above the building image)
            SchemaComponents\Section::make('Introduction Paragraphs')
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
                ->visible(fn (Get $get) => $get('slug') === 'new-subject'),

            // Section 3: Building image (shown between intro paragraphs and bullet list)
            SchemaComponents\Section::make('Building Image')
                ->description('Image shown below the 4 intro paragraphs')
                ->schema([
                    Forms\Components\FileUpload::make('meta.building_image')
                        ->label('Building Image')
                        ->image()
                        ->directory('pages/new-subject')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'new-subject'),

            // Section 4: Bullet list section
            SchemaComponents\Section::make('Bullet List Section')
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
                ->visible(fn (Get $get) => $get('slug') === 'new-subject'),

            // Section 5: Four closing paragraphs (after bullet list, before girls image)
            SchemaComponents\Section::make('Closing Paragraphs')
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
                ->visible(fn (Get $get) => $get('slug') === 'new-subject'),

            // Section 6: Girls image (bottom of page)
            SchemaComponents\Section::make('Girls Image')
                ->description('Image shown at the very bottom of the page')
                ->schema([
                    Forms\Components\FileUpload::make('meta.girls_image')
                        ->label('Girls Image')
                        ->image()
                        ->directory('pages/new-subject')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'new-subject'),

            // ── EBOOKS PAGE meta fields ─────────────────────────────────────
            SchemaComponents\Section::make('Background Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_image')
                        ->label('Page Background Image')
                        ->image()
                        ->directory('pages/ebooks')
                        ->helperText('If empty, defaults to /images/shutterstock_312912578_1.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'ebooks'),

            SchemaComponents\Section::make('Heading & Bullet Points')
                ->schema([
                    Forms\Components\TextInput::make('meta.recommend_heading')
                        ->label('Section Heading')
                        ->placeholder('We recommend buying eBooks (soft copies) If:')
                        ->columnSpanFull()
                        ->live()
                        ->hint(fn ($state) => (100 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 100 ? 'danger' : 'gray')
                        ->helperText('Default: "We recommend buying eBooks (soft copies) If:"'),
                    Forms\Components\RichEditor::make('meta.bullet_items')
                        ->label('Bullet Points')
                        ->helperText('Use the bullet list button in the toolbar. If empty, default bullet items are shown.')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'ebooks'),

            SchemaComponents\Section::make('Images')
                ->schema([
                    Forms\Components\FileUpload::make('meta.main_image')
                        ->label('Main Banner Image')
                        ->image()
                        ->directory('pages/ebooks')
                        ->helperText('If empty, defaults to /images/education_system_1.webp'),
                    Forms\Components\FileUpload::make('meta.boy_image')
                        ->label('Bottom Boy Image')
                        ->image()
                        ->directory('pages/ebooks')
                        ->helperText('If empty, defaults to /images/9866_1.png'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'ebooks'),

            SchemaComponents\Section::make('Catalog Button')
                ->schema([
                    Forms\Components\TextInput::make('meta.catalog_button_text')
                        ->label('Button Text')
                        ->placeholder('View full eBooks catalog')
                        ->helperText('Default: "View full eBooks catalog"'),
                    Forms\Components\TextInput::make('meta.catalog_button_url')
                        ->label('Button URL')
                        ->placeholder('/pricing')
                        ->helperText('Default: /pricing'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'ebooks'),

            // ── COUNSELLING PAGE meta fields ──────────────────────────────────
            SchemaComponents\Section::make('Section 1 – Title & Background')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_image_1')
                        ->label('Background Image')
                        ->image()
                        ->directory('pages/counselling')
                        ->helperText('Default: bulb-3.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-counselling-therapy'),

            SchemaComponents\Section::make('Section 2 – Credentials')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_image_2')
                        ->label('Background Image')
                        ->image()
                        ->directory('pages/counselling')
                        ->helperText('Default: conselling_1_small.webp (used in section 2 and section 4)')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('meta.credentials')
                        ->label('Credential Items')
                        ->helperText('Use the bullet list button to add each credential as a list item')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('meta.button_image')
                        ->label('Logotherapy Button Image')
                        ->image()
                        ->directory('pages/counselling')
                        ->helperText('Default: BUTTON_1.avif'),
                    Forms\Components\FileUpload::make('meta.man_image')
                        ->label('Bottom Image')
                        ->image()
                        ->directory('pages/counselling')
                        ->helperText('Default: Man.webp'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-counselling-therapy'),

            SchemaComponents\Section::make('Section 3 – Quote')
                ->schema([
                    Forms\Components\Textarea::make('meta.quote')
                        ->label('Quote Text')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (200 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 200 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-counselling-therapy'),

            SchemaComponents\Section::make('Section 4 – Life & Faith')
                ->schema([
                    Forms\Components\FileUpload::make('meta.life_image')
                        ->label('Top Image')
                        ->image()
                        ->directory('pages/counselling')
                        ->helperText('Default: Life-does.webp'),
                    Forms\Components\FileUpload::make('meta.icon_image')
                        ->label('Left Column Icon')
                        ->image()
                        ->directory('pages/counselling')
                        ->helperText('Default: icon_medium.avif'),
                    Forms\Components\Textarea::make('meta.therapy_para')
                        ->label('Paragraph')
                        ->rows(5)
                        ->live()
                        ->hint(fn ($state) => (450 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 450 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.faith_btn_text')
                        ->label('Faith Button Text')
                        ->live()
                        ->hint(fn ($state) => (80 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 80 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-counselling-therapy'),

            // ── TEACHERS TRAINING PAGE meta fields ───────────────────────────
            SchemaComponents\Section::make('Background Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_image')
                        ->label('Background Image')
                        ->image()
                        ->directory('pages/teachers-training')
                        ->helperText('Default: teacherstraining.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-teachers-training'),

            SchemaComponents\Section::make('Intro')
                ->schema([
                    Forms\Components\Textarea::make('meta.intro_para')
                        ->label('Intro Paragraph')
                        ->rows(4)
                        ->live()
                        ->hint(fn ($state) => (350 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 350 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-teachers-training'),

            SchemaComponents\Section::make('We Offer Two Types')
                ->schema([
                    Forms\Components\TextInput::make('meta.offer_heading')
                        ->label('Heading')
                        ->placeholder('We offer two types of training:')
                        ->live()
                        ->hint(fn ($state) => (60 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 60 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.offer_point_1')
                        ->label('Point 1')
                        ->rows(2)
                        ->live()
                        ->hint(fn ($state) => (150 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 150 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.offer_point_2')
                        ->label('Point 2')
                        ->rows(4)
                        ->live()
                        ->hint(fn ($state) => (400 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 400 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-teachers-training'),

            SchemaComponents\Section::make('Section 1: Customised Training')
                ->schema([
                    Forms\Components\TextInput::make('meta.section1_heading')
                        ->label('Section Heading')
                        ->live()
                        ->hint(fn ($state) => (100 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 100 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.section1_para_1')
                        ->label('Paragraph 1')
                        ->rows(4)
                        ->live()
                        ->hint(fn ($state) => (400 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 400 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.section1_para_2')
                        ->label('Paragraph 2')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (200 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 200 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.list_heading')
                        ->label('List Heading (bold)')
                        ->rows(2)
                        ->live()
                        ->hint(fn ($state) => (200 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 200 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('meta.bullet_list')
                        ->label('Bullet List')
                        ->helperText('Use the bullet list button in the toolbar')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.custom_courses_para')
                        ->label('Custom Courses Paragraph')
                        ->rows(2)
                        ->live()
                        ->hint(fn ($state) => (150 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 150 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-teachers-training'),

            SchemaComponents\Section::make('Source Code Academia')
                ->schema([
                    Forms\Components\Textarea::make('meta.sca_contact_para')
                        ->label('Contact Paragraph')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (200 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 200 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('meta.sca_image')
                        ->label('Source Code Academia Logo')
                        ->image()
                        ->directory('pages/teachers-training')
                        ->helperText('Default: source-code-academia.png')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-teachers-training'),

            SchemaComponents\Section::make('Section 2: ICE Publishers Training')
                ->schema([
                    Forms\Components\TextInput::make('meta.section2_heading')
                        ->label('Section Heading')
                        ->live()
                        ->hint(fn ($state) => (100 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 100 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.contact_para')
                        ->label('Contact Paragraph')
                        ->helperText('HTML allowed for links, e.g. <a href="/contact">Contact Us</a>')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.license_para')
                        ->label('License Paragraph')
                        ->rows(4)
                        ->live()
                        ->hint(fn ($state) => (350 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 350 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.more_info_para')
                        ->label('More Info Paragraph')
                        ->helperText('HTML allowed for links, e.g. <a href="/online-classes">Online Classes</a>')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-teachers-training'),

            SchemaComponents\Section::make('Bottom Meeting Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.meeting_image')
                        ->label('Meeting Image')
                        ->image()
                        ->directory('pages/teachers-training')
                        ->helperText('Default: meeting.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-teachers-training'),

            // ── AUDIO STORIES PAGE meta fields ───────────────────────────────
            SchemaComponents\Section::make('Background Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_image')
                        ->label('Background Image')
                        ->image()
                        ->directory('pages/audio-stories')
                        ->helperText('Default: audio_bg.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            SchemaComponents\Section::make('Intro')
                ->schema([
                    Forms\Components\Textarea::make('meta.intro_para')
                        ->label('Intro Paragraph')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (300 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 300 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            SchemaComponents\Section::make('Audio Sample')
                ->schema([
                    Forms\Components\TextInput::make('meta.audio_label')
                        ->label('Audio Label')
                        ->placeholder('Free sample of an audio story:')
                        ->live()
                        ->hint(fn ($state) => (60 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 60 ? 'danger' : 'gray'),
                    Forms\Components\TextInput::make('meta.audio_title')
                        ->label('Audio Title')
                        ->placeholder('The Ugly Duckling')
                        ->live()
                        ->hint(fn ($state) => (50 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 50 ? 'danger' : 'gray'),
                    Forms\Components\TextInput::make('meta.audio_url')
                        ->label('Audio File URL')
                        ->placeholder('https://...')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            SchemaComponents\Section::make('Quote & Citation')
                ->schema([
                    Forms\Components\Textarea::make('meta.quote')
                        ->label('Quote')
                        ->rows(4)
                        ->live()
                        ->hint(fn ($state) => (500 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 500 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.citation_1')
                        ->label('Citation')
                        ->live()
                        ->hint(fn ($state) => (150 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 150 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            SchemaComponents\Section::make('Narrative Transportation')
                ->schema([
                    Forms\Components\Textarea::make('meta.narrative_para')
                        ->label('Paragraph')
                        ->rows(5)
                        ->live()
                        ->hint(fn ($state) => (750 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 750 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.citation_2')
                        ->label('Citation')
                        ->live()
                        ->hint(fn ($state) => (300 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 300 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            SchemaComponents\Section::make('Imagine Section')
                ->schema([
                    Forms\Components\Textarea::make('meta.imagine_para')
                        ->label('Paragraph')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (300 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 300 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.blue_quote')
                        ->label('Blue Quote')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (350 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 350 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.citation_3')
                        ->label('Citation')
                        ->live()
                        ->hint(fn ($state) => (50 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 50 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            SchemaComponents\Section::make('Availability & Course Boxes')
                ->schema([
                    Forms\Components\TextInput::make('meta.availability_line')
                        ->label('Availability Line')
                        ->placeholder('Audio Stories available in Key Stage 1 and Key Stage 02')
                        ->live()
                        ->hint(fn ($state) => (100 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 100 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('meta.course_box_links')
                        ->label('Course Box Links')
                        ->helperText('e.g. "Or buy with course box of choice: <a>Grade 1</a> ..."')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            SchemaComponents\Section::make('Headphone Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.headphone_image')
                        ->label('Headphone Image')
                        ->image()
                        ->directory('pages/audio-stories')
                        ->helperText('Default: headphone.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'audio-stories'),

            // ── HOMESCHOOLING PAGE meta fields ───────────────────────────────
            SchemaComponents\Section::make('Intro Paragraphs')
                ->schema([
                    Forms\Components\Textarea::make('meta.intro_para_1')
                        ->label('Intro Paragraph 1')
                        ->rows(4)
                        ->live()
                        ->hint(fn ($state) => (400 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 400 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta.intro_para_2')
                        ->label('Intro Paragraph 2')
                        ->helperText('You may include HTML for links, e.g. <a href="...">clicking here</a>')
                        ->rows(4)
                        ->live()
                        ->hint(fn ($state) => (250 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 250 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-homeschooling'),

            SchemaComponents\Section::make('Helping Hand Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.helping_hand_image')
                        ->label('Helping Hand Image')
                        ->image()
                        ->directory('pages/homeschooling')
                        ->helperText('Default: helpinghand.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-homeschooling'),

            SchemaComponents\Section::make('Laptop Section')
                ->schema([
                    Forms\Components\Textarea::make('meta.laptop_text')
                        ->label('Laptop Text')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (150 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 150 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('meta.laptop_image')
                        ->label('Laptop Image')
                        ->image()
                        ->directory('pages/homeschooling')
                        ->helperText('Default: claptop.webp (shown on mobile only)')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'feature-homeschooling'),

            // ── HARD COPIES PAGE meta fields ─────────────────────────────────
            SchemaComponents\Section::make('Recommend Section')
                ->schema([
                    Forms\Components\TextInput::make('meta.list_heading')
                        ->label('Section Heading')
                        ->placeholder('We recommend buying Hard Copies If:')
                        ->columnSpanFull()
                        ->live()
                        ->hint(fn ($state) => (100 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 100 ? 'danger' : 'gray'),
                    Forms\Components\RichEditor::make('meta.bullet_items')
                        ->label('Bullet Points')
                        ->helperText('Use the editor to create a bulleted list')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'hard-copies'),

            SchemaComponents\Section::make('Alert & Shipping')
                ->schema([
                    Forms\Components\TextInput::make('meta.alert_text')
                        ->label('Alert Box Text')
                        ->placeholder('To see your selected hard copy/book price, please click the "Hard copy" link.')
                        ->columnSpanFull()
                        ->live()
                        ->hint(fn ($state) => (200 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 200 ? 'danger' : 'gray'),
                    Forms\Components\TextInput::make('meta.shipping_text')
                        ->label('Shipping Notice Text')
                        ->placeholder('Shipping/Transportation charges apply.')
                        ->columnSpanFull()
                        ->live()
                        ->hint(fn ($state) => (80 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 80 ? 'danger' : 'gray'),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'hard-copies'),

            SchemaComponents\Section::make('Checkout Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.checkout_image')
                        ->label('Checkout Screenshot Image')
                        ->image()
                        ->directory('pages/hard-copies')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('slug') === 'hard-copies'),

            SchemaComponents\Section::make('Column Labels & Images')
                ->schema([
                    Forms\Components\TextInput::make('meta.left_label')
                        ->label('Left Column Label')
                        ->placeholder('Textbooks for Primary'),
                    Forms\Components\TextInput::make('meta.right_label')
                        ->label('Right Column Label')
                        ->placeholder("Teacher's Guides for Primary"),
                    Forms\Components\FileUpload::make('meta.left_image')
                        ->label('Left Column Image')
                        ->image()
                        ->directory('pages/hard-copies'),
                    Forms\Components\FileUpload::make('meta.right_image')
                        ->label('Right Column Image')
                        ->image()
                        ->directory('pages/hard-copies'),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('slug') === 'hard-copies'),

            // ── ONLINE CLASSES PAGE meta fields ─────────────────────────────
            SchemaComponents\Section::make('Background Images')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_image')
                        ->label('Page Background Image')
                        ->image()
                        ->directory('pages/online-classes')
                        ->helperText('If empty, defaults to /images/bg_jpg.webp'),
                    Forms\Components\FileUpload::make('meta.banner_image')
                        ->label('Middle Banner Image')
                        ->image()
                        ->directory('pages/online-classes')
                        ->helperText('If empty, defaults to /images/online_11.webp'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'online-classes'),


            SchemaComponents\Section::make('Intro Block')
                ->schema([
                    Forms\Components\Textarea::make('meta.intro_text')
                        ->label('Intro Paragraph')
                        ->rows(3)
                        ->live()
                        ->hint(fn ($state) => (534 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 534 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.launching_text')
                        ->label('Launching Text')
                        ->placeholder('LAUNCHING SOON!')
                        ->live()
                        ->hint(fn ($state) => (30 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 30 ? 'danger' : 'gray'),
                    Forms\Components\TextInput::make('meta.online_teachers_heading')
                        ->label('Online Teachers Heading')
                        ->placeholder('ONLINE TEACHERS')
                        ->live()
                        ->hint(fn ($state) => (30 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 30 ? 'danger' : 'gray'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'online-classes'),

            SchemaComponents\Section::make('Teach With Us')
                ->schema([
                    Forms\Components\TextInput::make('meta.teach_with_us_heading')
                        ->label('Heading')
                        ->placeholder('Teach with Us')
                        ->live()
                        ->hint(fn ($state) => (33 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 33 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('meta.teach_with_us_content')
                        ->label('Content')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'online-classes'),

            SchemaComponents\Section::make('Content Sections')
                ->schema([
                    Forms\Components\Repeater::make('meta.sections')
                        ->label(false)
                        ->schema([
                            Forms\Components\TextInput::make('heading')
                                ->label('Section Heading')
                                ->live()
                                ->hint(fn ($state) => (50 - mb_strlen($state ?? '')) . ' characters remaining')
                                ->hintColor(fn ($state) => mb_strlen($state ?? '') > 50 ? 'danger' : 'gray')
                                ->columnSpanFull(),
                            Forms\Components\RichEditor::make('content')
                                ->label('Section Content')
                                ->columnSpanFull(),
                        ])
                        ->addActionLabel('+ Add Section')
                        ->collapsible()
                        ->collapsed()
                        ->reorderableWithButtons()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'online-classes'),

            SchemaComponents\Section::make('Classroom Image')
                ->schema([
                    Forms\Components\FileUpload::make('meta.classroom_image')
                        ->label('Bottom Classroom Image')
                        ->image()
                        ->directory('pages/online-classes')
                        ->helperText('If empty, defaults to /images/classroom.webp')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'online-classes'),

            // ── CONTACT PAGE meta fields ────────────────────────────────────
            SchemaComponents\Section::make('Section 1 – Header')
                ->schema([
                    Forms\Components\FileUpload::make('meta.bg_image')
                        ->label('Background Image')
                        ->image()
                        ->directory('pages/contact')
                        ->helperText('Default: WhatsApp_Image_2024-10-21_at_17.35.56.webp')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.email_line')
                        ->label('Email Line')
                        ->placeholder('Email us at: info@characterbuilding.education')
                        ->live()
                        ->hint(fn ($state) => (80 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 80 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.form_prompt')
                        ->label('Form Prompt Text')
                        ->placeholder('Or use the Form below:')
                        ->live()
                        ->hint(fn ($state) => (60 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 60 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'contact'),

            SchemaComponents\Section::make('Section 2 – Contact Info Columns')
                ->schema([
                    Forms\Components\TextInput::make('meta.col1_heading')
                        ->label('Column 1 Heading')
                        ->placeholder('International customers')
                        ->live()
                        ->hint(fn ($state) => (50 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 50 ? 'danger' : 'gray'),
                    Forms\Components\TextInput::make('meta.col1_text')
                        ->label('Column 1 WhatsApp Text')
                        ->placeholder('0045 50106941 (Only WhatsApp calls)')
                        ->live()
                        ->hint(fn ($state) => (80 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 80 ? 'danger' : 'gray'),
                    Forms\Components\TextInput::make('meta.col2_heading')
                        ->label('Column 2 Heading')
                        ->placeholder('Pakistan')
                        ->live()
                        ->hint(fn ($state) => (50 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 50 ? 'danger' : 'gray'),
                    Forms\Components\RichEditor::make('meta.col2_content')
                        ->label('Column 2 Content')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('meta.col3_heading')
                        ->label('Column 3 Heading')
                        ->placeholder('Urdu website')
                        ->live()
                        ->hint(fn ($state) => (50 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 50 ? 'danger' : 'gray'),
                    Forms\Components\RichEditor::make('meta.col3_content')
                        ->label('Column 3 Content')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'contact'),

            SchemaComponents\Section::make('Section 3 – Form Card')
                ->schema([
                    Forms\Components\TextInput::make('meta.form_intro')
                        ->label('Form Intro Text')
                        ->placeholder('For questions and suggestions, please get in touch with us at:')
                        ->live()
                        ->hint(fn ($state) => (150 - mb_strlen($state ?? '')) . ' characters remaining')
                        ->hintColor(fn ($state) => mb_strlen($state ?? '') > 150 ? 'danger' : 'gray')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('slug') === 'contact'),

            // ── SEO ─────────────────────────────────────────────────────────
            SchemaComponents\Section::make('SEO')->schema([
                Forms\Components\TextInput::make('meta_title')->maxLength(255),
                Forms\Components\Textarea::make('meta_description')->columnSpanFull()->rows(2),
                Forms\Components\TextInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->placeholder('character education, moral education, islamic curriculum')
                    ->helperText('Comma-separated keywords for search engines')
                    ->columnSpanFull(),
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
            ->actions([Actions\EditAction::make()])
            ->bulkActions([
                Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()]),
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
