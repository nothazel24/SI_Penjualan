<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\AccountWidget as BaseAccountWidget;

class CustomAccountWidget extends BaseAccountWidget
{
    // set width to full
    protected int | string |array $columnSpan = 'full';

    // custom widget UI
    protected static string $view = 'filament.widgets.custom-account-widget';

    // greetings
    public function getGreeting(): string
    {
        $hour = Carbon::now()->hour;

        return match (true) {
            $hour >= 5 && $hour <= 11 => 'Selamat Pagi',
            $hour >= 11 && $hour <= 15 => 'Selamat Siang',
            $hour >= 15 && $hour <= 18 => 'Selamat Sore',
            default => 'Selamat malam'
        };

        // debugging (jam)
        // dd($hour, getType($hour));
    }
}
