<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persetujuan (acc) & pembayaran atas permintaan, per produsen per
     * permintaan (satu pembayaran = satu produsen dalam satu permintaan).
     */
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_bahan_baku_id')->constrained('permintaan_bahan_bakus')->restrictOnDelete();
            $table->foreignId('produsen_id')->constrained('produsens')->restrictOnDelete();
            $table->foreignId('pemilik_id')->constrained('pemiliks')->restrictOnDelete();
            $table->decimal('jumlah_bayar', 12, 2);
            $table->date('tanggal_bayar')->nullable();
            $table->enum('status_acc', ['menunggu', 'disetujui', 'ditolak']);
            $table->enum('status_bayar', ['belum', 'lunas']);
            $table->string('metode_bayar', 50)->nullable();
            $table->timestamps();

            $table->unique(['permintaan_bahan_baku_id', 'produsen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
