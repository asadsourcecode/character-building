<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(User::count()),

            'admin' => Tab::make('Admin')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'admin'))
                ->badge(User::where('role', 'admin')->count()),

            'teacher' => Tab::make('Teacher')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'teacher'))
                ->badge(User::where('role', 'teacher')->count()),

            'student' => Tab::make('Student')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'student'))
                ->badge(User::where('role', 'student')->count()),
        ];
    }

    // Tabs are rendered as toolbar actions inside the table (see UserResource::table())
    // instead of the default standalone pill row, so keep getTabs()'s filtering logic
    // but drop its normal visual output here.
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }
}
