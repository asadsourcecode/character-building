<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FrontendLogResource\Pages;
use App\Models\UserLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FrontendLogResource extends Resource
{
    protected static ?string $model = UserLog::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Frontend Logs';
    protected static string|\UnitEnum|null $navigationGroup = 'Logs';
    protected static ?string $slug = 'frontend-logs';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y, h:i A')
                    ->timezone('Asia/Karachi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'registered' => 'success',
                        'login'      => 'info',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Browser / Device')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->user_agent),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'registered' => 'Registered',
                        'login'      => 'Login',
                    ]),
            ])
            ->bulkActions([])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFrontendLogs::route('/'),
        ];
    }

    public static function canCreate(): bool { return false; }
}
