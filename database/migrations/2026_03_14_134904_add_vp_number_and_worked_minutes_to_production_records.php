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
        Schema::table('production_records', function (Blueprint $table) {
            $table->string('vp_number')->nullable()->after('order_number');
            $table->integer('worked_minutes')->nullable()->after('total_paused_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->dropColumn(['vp_number', 'worked_minutes']);
        });
    }
};
