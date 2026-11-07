<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bukti transfer / bukti pembayaran tunai (satu per tagihan)
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('bukti_bayar')->nullable()->after('metode_bayar');
        });

        // Gambar receipt yang sudah dicetak (satu receipt per penerimaan)
        Schema::table('penerimaan_bahan_bakus', function (Blueprint $table) {
            $table->string('foto_receipt')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('bukti_bayar');
        });

        Schema::table('penerimaan_bahan_bakus', function (Blueprint $table) {
            $table->dropColumn('foto_receipt');
        });
    }
};
