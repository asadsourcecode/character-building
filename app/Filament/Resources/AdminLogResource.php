<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminLogResource\Pages;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class AdminLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Admin Logs';
    protected static string|\UnitEnum|null $navigationGroup = 'Logs';
    protected static ?string $slug = 'admin-logs';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y, h:i A')
                    ->timezone('Asia/Karachi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Admin')
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Action')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('log_name')
                    ->label('Resource Type')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject.title')
                    ->label('Record')
                    ->default(fn ($record) => $record->subject?->name ?? $record->subject?->id ?? '—')
                    ->limit(40),

                Tables\Columns\TextColumn::make('properties')
                    ->label('Changes')
                    ->formatStateUsing(function ($record) {
                        $old = $record->properties['old'] ?? [];
                        $new = $record->properties['attributes'] ?? [];
                        if (empty($old) && empty($new)) return '—';
                        $keys = array_keys(array_merge($old, $new));
                        return implode(', ', array_slice($keys, 0, 5)) . (count($keys) > 5 ? '...' : '');
                    })
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),

                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Resource Type')
                    ->options([
                        'page'             => 'Page',
                        'product'          => 'Product',
                        'product_section'  => 'Product Section',
                        'product_category' => 'Product Category',
                        'product_package'  => 'Product Package',
                    ]),
            ])
            ->actions([
                Actions\Action::make('view_changes')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Change Details')
                    ->modalContent(fn (Activity $record) => view('filament.admin-log-detail', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->bulkActions([])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminLogs::route('/'),
        ];
    }

    public static function canCreate(): bool { return false; }
}
