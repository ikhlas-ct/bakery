<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detail item pemakaian, tertaut opsional ke batch penerimaan spesifik
     * (penerimaan_bahan_baku_detail_id) untuk penelusuran FEFO.
     */
    public function up(): void
    {
        Schema::create('pemakaian_bahan_baku_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemakaian_bahan_baku_id')->constrained('pemakaian_bahan_bakus')->restrictOnDelete();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->restrictOnDelete();
            $table->foreignId('penerimaan_bahan_baku_detail_id')->nullable()
                ->constrained('penerimaan_bahan_baku_details', 'id', 'pemakaian_detail_penerimaan_detail_fk')
                ->restrictOnDelete();
            $table->decimal('jumlah_dipakai', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemakaian_bahan_baku_details');
    }
};
