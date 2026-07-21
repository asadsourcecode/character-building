<?php

namespace App\Providers;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        FileUpload::configureUsing(function (FileUpload $upload): void {
            $upload->disk('public');
        });
    }
}
