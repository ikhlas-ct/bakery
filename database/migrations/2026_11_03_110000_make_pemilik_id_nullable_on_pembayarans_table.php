<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tagihan pembayaran kini dibuat otomatis saat Admin mencatat penerimaan bahan baku.
 * Pembayaran bisa diproses oleh admin maupun pemilik, jadi pemilik_id harus nullable
 * (baru terisi bila pemilik yang memprosesnya).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unsignedBigInteger('pemilik_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unsignedBigInteger('pemilik_id')->nullable(false)->change();
        });
    }
};
