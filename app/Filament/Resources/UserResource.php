<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\Select::make('role')
                ->options([
                    'admin'   => 'Admin',
                    'teacher' => 'Teacher',
                    'student' => 'Student',
                ])
                ->required()
                ->default('teacher')
                ->live(),

            Forms\Components\Select::make('class_id')
                ->label('Assigned Class')
                ->options(fn () => ClassRoom::pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('subjects', []))
                ->visible(fn (Get $get) => $get('role') === 'student'),

            Forms\Components\Select::make('subjects')
                ->label('Assigned Subjects')
                ->relationship('subjects', 'name')
                ->options(fn (Get $get) => $get('class_id')
                    ? Subject::where('class_id', $get('class_id'))->pluck('name', 'id')
                    : [])
                ->multiple()
                ->searchable()
                ->live()
                ->disabled(fn (Get $get) => ! $get('class_id'))
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $subjectIds = $get('subjects') ?? [];
                    $teacherId = $get('teacher_id');

                    if ($teacherId && $subjectIds && ! User::find($teacherId)?->teachingSubjects()->whereIn('subjects.id', $subjectIds)->exists()) {
                        $set('teacher_id', null);
                    }
                })
                ->helperText('Only subjects offered in the assigned class are selectable.')
                ->visible(fn (Get $get) => $get('role') === 'student'),

            Forms\Components\Select::make('teacher_id')
                ->label('Assigned Teacher')
                ->options(function (Get $get) {
                    $subjectIds = $get('subjects') ?? [];

                    return $subjectIds
                        ? User::where('role', 'teacher')->whereHas('teachingSubjects', fn ($query) => $query->whereIn('subjects.id', $subjectIds))->pluck('name', 'id')
                        : User::where('role', 'teacher')->pluck('name', 'id');
                })
                ->searchable()
                ->visible(fn (Get $get) => $get('role') === 'student')
                ->helperText('Limited to teachers who teach at least one of the selected subjects. This student\'s books and reading progress will be visible on the assigned teacher\'s dashboard.'),

            Forms\Components\Select::make('teachingSubjects')
                ->label('Teaching Subjects')
                ->relationship('teachingSubjects', 'name')
                ->multiple()
                ->preload()
                ->searchable()
                ->helperText('This teacher will teach these subjects across every class that offers them.')
                ->visible(fn (Get $get) => $get('role') === 'teacher'),

            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context) => $context === 'create')
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin'   => 'danger',
                        'teacher' => 'warning',
                        'student' => 'success',
                        default   => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Assigned Teacher')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('classRoom.name')
                    ->label('Assigned Class')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subjects.name')
                    ->label('Assigned Subjects')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('teachingSubjects.name')
                    ->label('Teaching Subjects')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->toolbarActions([
                static::roleTabAction('all', 'All', fn () => User::count()),
                static::roleTabAction('admin', 'Admin', fn () => User::where('role', 'admin')->count()),
                static::roleTabAction('teacher', 'Teacher', fn () => User::where('role', 'teacher')->count()),
                static::roleTabAction('student', 'Student', fn () => User::where('role', 'student')->count()),
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    protected static function roleTabAction(string $tab, string $label, \Closure $count): Actions\Action
    {
        return Actions\Action::make('tab_' . $tab)
            ->label(fn () => new HtmlString(
                $label . ' <span class="fi-badge fi-color-gray fi-size-xs" style="margin-inline-start: .375rem;">' . $count() . '</span>'
            ))
            ->link()
            ->extraAttributes(fn ($livewire) => [
                'class' => 'fi-tabs-item' . ((($livewire->activeTab ?? 'all') === $tab) ? ' fi-active' : ''),
            ])
            ->action(function ($livewire) use ($tab) {
                $livewire->activeTab = $tab;
                $livewire->updatedActiveTab();
            });
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
