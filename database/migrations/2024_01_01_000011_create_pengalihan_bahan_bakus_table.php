<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengalihan permintaan ke produsen lain / bahan baku pengganti setara,
     * dengan siklus approval sendiri lewat status_produsen_pengganti.
     */
    public function up(): void
    {
        Schema::create('pengalihan_bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_bahan_baku_detail_id')->constrained('permintaan_bahan_baku_details')->restrictOnDelete();
            $table->foreignId('bahan_baku_asal_id')->constrained('bahan_bakus')->restrictOnDelete();
            $table->foreignId('bahan_baku_pengganti_id')->nullable()->constrained('bahan_bakus')->restrictOnDelete();
            $table->foreignId('produsen_pengganti_id')->nullable()->constrained('produsens')->restrictOnDelete();
            $table->decimal('jumlah_dialihkan', 10, 2);
            $table->enum('status_produsen_pengganti', ['pending', 'disetujui', 'disetujui_sebagian', 'ditolak']);
            $table->decimal('jumlah_disetujui_pengganti', 10, 2)->nullable();
            $table->string('alasan_pengalihan', 255);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengalihan_bahan_bakus');
    }
};
