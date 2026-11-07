<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pemilik kini boleh mencatat penerimaan bahan baku (sama seperti admin).
 * Pencatat penerimaan = admin_id ATAU pemilik_id, jadi admin_id dibuat nullable
 * dan ditambah kolom pemilik_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_bahan_bakus', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->change();

            $table->foreignId('pemilik_id')
                ->nullable()
                ->after('admin_id')
                ->constrained('pemiliks')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_bahan_bakus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pemilik_id');
        });

        // Penerimaan yang dicatat pemilik tidak punya admin; hapus/isi dulu sebelum rollback
        // bila ingin mengembalikan admin_id menjadi NOT NULL.
        // Schema::table('penerimaan_bahan_bakus', fn (Blueprint $t) => $t->unsignedBigInteger('admin_id')->nullable(false)->change());
    }
};
