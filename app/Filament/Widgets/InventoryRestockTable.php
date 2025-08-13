<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\ExitItem;
use App\Models\Inventory;
use Filament\Tables\Table;
use App\Models\IncomingItem;
use App\Models\StockHistory;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class InventoryRestockTable extends BaseWidget
{
    protected static ?string $heading = 'Reorder Point & Rekomendasi Restock';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Inventory::query())
            ->columns([
                TextColumn::make('nama_barang')->label('Barang'),

                TextColumn::make('avg_usage')->label('Avg Usage')->getStateUsing(
                    fn ($record) => $this->avgUsage($record->id)
                ),

                TextColumn::make('max_usage')->label('Max Usage')->getStateUsing(
                    fn ($record) => $this->maxUsage($record->id)
                ),

                TextColumn::make('avg_lt')->label('Avg LT')->getStateUsing(
                    fn ($record) => $this->avgLeadTime($record->id) * -1
                ),

                TextColumn::make('max_lt')->label('Max LT')->getStateUsing(
                    fn ($record) => $this->maxLeadTime($record->id) * -1
                ),

                TextColumn::make('safety_stock')->label('Safety Stock')->getStateUsing(
                    fn ($record) => $this->safetyStock($record->id) * -1
                ),

                TextColumn::make('reorder_point')->label('ROP')->getStateUsing(
                    fn ($record) => $this->reorderPoint($record->id) * -1
                ),

                TextColumn::make('stok')->label('Stok')->getStateUsing(
                    fn ($record) => $this->currentStock($record->id)
                ),

                TextColumn::make('restock')->label('Saran Restock')->getStateUsing( 
                    // fn ($record) => max($this->reorderPoint($record->id) - $this->currentStock($record->id), 0)
                    fn ($record) => ($this->reorderPoint($record->id) - ($this->currentStock($record->id) *-1) ) *-1
                ),
            ]);
    }

    // Helper methods
    private function avgUsage($inventoryId)
    {
        return ExitItem::where('inventory_id', $inventoryId)
            ->selectRaw('DATE(tanggal_keluar) as tgl, SUM(jumlah) as total')
            ->groupBy('tgl')
            ->get()
            ->avg('total') ?? 0;
    }

    private function maxUsage($inventoryId)
    {
        return ExitItem::where('inventory_id', $inventoryId)
            ->selectRaw('DATE(tanggal_keluar) as tgl, SUM(jumlah) as total')
            ->groupBy('tgl')
            ->get()
            ->max('total') ?? 0;
    }

    private function avgLeadTime($inventoryId)
    {
        return IncomingItem::where('inventory_id', $inventoryId)
            ->get()
            ->avg(fn($i) => Carbon::parse($i->tanggal_masuk)->diffInDays(Carbon::parse($i->tanggal_pesan))) ?? 0;    
    }

    private function maxLeadTime($inventoryId)
    {
        return IncomingItem::where('inventory_id', $inventoryId)
            ->get()
            ->max(fn($i) => Carbon::parse($i->tanggal_masuk)->diffInDays(Carbon::parse($i->tanggal_pesan))) ?? 0;
    }

    private function safetyStock($inventoryId)
    {
        return ($this->maxUsage($inventoryId) * $this->maxLeadTime($inventoryId)) -
               ($this->avgUsage($inventoryId) * $this->avgLeadTime($inventoryId));
    }

    private function reorderPoint($inventoryId)
    {
        return ($this->avgUsage($inventoryId) * $this->avgLeadTime($inventoryId)) +
               $this->safetyStock($inventoryId);
    }

    private function currentStock($inventoryId)
    {
        return StockHistory::where('inventory_id', $inventoryId)
            ->latest('tanggal_transaksi')
            ->value('sisa_stok') ?? 0;
    }
}