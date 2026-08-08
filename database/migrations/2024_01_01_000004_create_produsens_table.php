<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data diri Produsen (mitra pemasok bahan baku), relasi one-to-one ke users.
     */
    public function up(): void
    {
        Schema::create('produsens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('nama_produsen', 100);
            $table->string('alamat', 255);
            $table->string('no_telp', 20);
            $table->enum('status_mitra', ['aktif', 'nonaktif']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produsens');
    }
};
