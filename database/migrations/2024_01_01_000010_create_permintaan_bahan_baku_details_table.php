<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detail per item permintaan: bahan baku, produsen tujuan, dan status
     * persetujuan per item (tiap produsen menilai bagiannya sendiri).
     */
    public function up(): void
    {
        Schema::create('permintaan_bahan_baku_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_bahan_baku_id')->constrained('permintaan_bahan_bakus')->restrictOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->restrictOnDelete();
            $table->foreignId('produsen_id')->constrained('produsens')->restrictOnDelete();
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('jumlah_diminta', 10, 2);
            $table->decimal('jumlah_disetujui', 10, 2)->nullable();
            $table->enum('status_produsen', ['pending', 'disetujui', 'disetujui_sebagian', 'ditolak']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_bahan_baku_details');
    }
};
