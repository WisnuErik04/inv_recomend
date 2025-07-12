<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Statistics extends BaseWidget
{
    protected function getStats(): array
    {
        $pegawaiCount = User::whereNot('email', 'admin@mail.com')->count();
        return [
            Stat::make('Jumlah Pegawai', $pegawaiCount),
            Stat::make('Bounce rate', '21%'),
            Stat::make('Average time on page', '3:12'),
        ];
    }
}
