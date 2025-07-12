<?php

namespace App\Filament\Widgets;

use App\Models\ExitItem;
use App\Models\IncomingItem;
use Filament\Widgets\ChartWidget;

class InventoryBarChart extends ChartWidget
{
    protected static ?string $heading = 'Barang Masuk vs Keluar';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;
    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(now()->subMonths(5)->monthsUntil(now()));

        $incoming = IncomingItem::selectRaw('MONTH(tanggal_masuk) as bulan, SUM(jumlah) as total')
            ->whereBetween('tanggal_masuk', [now()->subMonths(5), now()])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $exit = ExitItem::selectRaw('MONTH(tanggal_keluar) as bulan, SUM(jumlah) as total')
            ->whereBetween('tanggal_keluar', [now()->subMonths(5), now()])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $labels = $months->map(fn($date) => $date->format('F'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk',
                    'data' => $months->map(fn($date) => $incoming[$date->month] ?? 0)->toArray(),
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Barang Keluar',
                    'data' => $months->map(fn($date) => $exit[$date->month] ?? 0)->toArray(),
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
