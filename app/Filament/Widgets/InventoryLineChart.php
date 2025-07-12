<?php

namespace App\Filament\Widgets;

use App\Models\StockHistory;
use Filament\Widgets\ChartWidget;

class InventoryLineChart extends ChartWidget
{
    protected static ?string $heading = 'Stok Harian';
    protected static ?int $sort = 1;
    protected static bool $isLazy = false; // agar langsung tampil

    protected function getData(): array
    {
        $data = StockHistory::selectRaw('DATE(tanggal_transaksi) as tgl, SUM(sisa_stok) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Stok Harian',
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                ],
            ],
            'labels' => $data->pluck('tgl')->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
