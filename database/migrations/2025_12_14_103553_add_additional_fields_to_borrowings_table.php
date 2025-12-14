<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->string('foto_ktm')->nullable()->after('item_id'); // KTM photo path
            $table->text('kondisi')->nullable()->after('catatan'); // Condition description for return
            $table->string('foto_kondisi')->nullable()->after('kondisi'); // Condition photo path for return
            $table->integer('lama_hari')->default(7)->after('tanggal_kembali'); // Duration in days
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn(['foto_ktm', 'kondisi', 'foto_kondisi', 'lama_hari']);
        });
    }
};
