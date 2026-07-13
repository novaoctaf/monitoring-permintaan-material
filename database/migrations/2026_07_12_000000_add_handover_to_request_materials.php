<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan alur "serah terima barang":
     * - handed_over_* : barang diserahkan oleh store (stok utama berkurang)
     * - received_*    : barang diterima oleh produksi (stok produksi bertambah)
     */
    public function up(): void
    {
        Schema::table('request_materials', function (Blueprint $table) {
            $table->foreignId('handed_over_by')->nullable()->after('approved_at')->constrained('users');
            $table->timestamp('handed_over_at')->nullable()->after('handed_over_by');
            $table->foreignId('received_by')->nullable()->after('handed_over_at')->constrained('users');
            $table->timestamp('received_at')->nullable()->after('received_by');
        });

        // Backfill data lama: permintaan yang sudah "approved" pada sistem lama
        // stoknya sudah berkurang saat persetujuan, jadi anggap sudah diserahkan
        // sekaligus diterima agar stok produksi tetap konsisten.
        DB::table('request_materials')
            ->where('status', 'approved')
            ->update([
                'handed_over_by' => DB::raw('approved_by'),
                'handed_over_at' => DB::raw('approved_at'),
                'received_by'    => DB::raw('requested_by'),
                'received_at'    => DB::raw('approved_at'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_materials', function (Blueprint $table) {
            $table->dropForeign(['handed_over_by']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['handed_over_by', 'handed_over_at', 'received_by', 'received_at']);
        });
    }
};
