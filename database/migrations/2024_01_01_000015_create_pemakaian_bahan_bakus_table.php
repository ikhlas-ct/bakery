<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HEADER transaksi pemakaian bahan baku oleh gudang.
     */
    public function up(): void
    {
        Schema::create('pemakaian_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->restrictOnDelete();
            $table->string('nomor_transaksi', 255)->unique();
            $table->date('tanggal_pakai');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemakaian_bahan_bakus');
    }
};
