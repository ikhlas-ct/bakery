<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detail per-batch penerimaan: jumlah diterima, sisa (jumlah_tersisa),
     * dan tanggal_kadaluarsa untuk mendukung FEFO.
     */
    public function up(): void
    {
        Schema::create('penerimaan_bahan_baku_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_bahan_baku_id')->constrained('penerimaan_bahan_bakus')->restrictOnDelete();
            $table->foreignId('permintaan_bahan_baku_detail_id')->nullable()
                ->constrained('permintaan_bahan_baku_details', 'id', 'penerimaan_detail_permintaan_detail_fk')
                ->restrictOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->restrictOnDelete();
            $table->decimal('jumlah_diterima', 10, 2);
            $table->decimal('jumlah_tersisa', 10, 2);
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_bahan_baku_details');
    }
};
