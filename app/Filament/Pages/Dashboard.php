<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\CustomAccountWidget;
use App\Filament\Widgets\SalesStatsOne;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Reports Dashboard';

    public function getWidgets(): array
    {
        return [
            CustomAccountWidget::class,
            SalesStatsOne::class
        ];
    }
}
