<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot many-to-many bahan_bakus <-> produsens, menyimpan harga per satuan
     * yang bisa berbeda-beda antar produsen untuk bahan baku yang sama.
     */
    public function up(): void
    {
        Schema::create('bahan_baku_produsens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->restrictOnDelete();
            $table->foreignId('produsen_id')->constrained('produsens')->restrictOnDelete();
            $table->decimal('harga', 12, 2);
            $table->timestamps();

            $table->unique(['bahan_baku_id', 'produsen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_baku_produsens');
    }
};
