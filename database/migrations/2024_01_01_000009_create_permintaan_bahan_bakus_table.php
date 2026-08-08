<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HEADER transaksi permintaan bahan baku. Detail per item ada di
     * permintaan_bahan_baku_details.
     */
    public function up(): void
    {
        Schema::create('permintaan_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->restrictOnDelete();
            $table->string('nomor_transaksi', 255)->unique();
            $table->date('tanggal_permintaan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_bahan_bakus');
    }
};
