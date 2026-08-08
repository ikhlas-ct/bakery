<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master data bahan baku + stok gudang bakery (stok_saat_ini adalah cache
     * dari SUM(jumlah_tersisa) pada penerimaan_bahan_baku_details).
     */
    public function up(): void
    {
        Schema::create('bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bahan_baku', 20)->unique();
            $table->string('nama_bahan_baku', 100);
            $table->foreignId('kategori_barang_id')->constrained('kategori_barangs')->restrictOnDelete();
            $table->foreignId('satuan_id')->constrained('satuans')->restrictOnDelete();
            $table->decimal('stok_saat_ini', 10, 2);
            $table->decimal('stok_minimum', 10, 2);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_bakus');
    }
};
