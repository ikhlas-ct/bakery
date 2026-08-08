<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HEADER transaksi penerimaan fisik bahan baku dari produsen ke gudang.
     * Satu pengiriman = satu produsen (produsen_id di header).
     */
    public function up(): void
    {
        Schema::create('penerimaan_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->restrictOnDelete();
            $table->foreignId('produsen_id')->constrained('produsens')->restrictOnDelete();
            $table->string('nomor_transaksi', 255)->unique();
            $table->date('tanggal_terima');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_bahan_bakus');
    }
};
