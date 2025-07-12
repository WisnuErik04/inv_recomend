<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exit_items', function (Blueprint $table) {
            $table->decimal('harga', 12, 0)->after('jumlah');
            $table->decimal('total', 14, 0)->after('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exit_items', function (Blueprint $table) {
            $table->dropColumn(['harga', 'total']);
        });
    }
};
