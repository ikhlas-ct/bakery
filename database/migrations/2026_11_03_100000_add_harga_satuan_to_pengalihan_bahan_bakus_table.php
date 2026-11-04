<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot harga per satuan dari bahan_baku_produsens saat admin menentukan
     * pengganti, agar nilai tagihan historis tidak berubah bila harga di-update.
     * NULLABLE karena baris pengalihan dibuat otomatis sebelum pengganti ditentukan.
     */
    public function up(): void
    {
        Schema::table('pengalihan_bahan_bakus', function (Blueprint $table) {
            $table->decimal('harga_satuan', 12, 2)->nullable()->after('jumlah_dialihkan');
        });
    }

    public function down(): void
    {
        Schema::table('pengalihan_bahan_bakus', function (Blueprint $table) {
            $table->dropColumn('harga_satuan');
        });
    }
};
