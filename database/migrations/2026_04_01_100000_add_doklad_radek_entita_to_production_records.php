<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->integer('doklad_radek_entita')->nullable()->after('doklad_id');
            $table->string('order_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->dropColumn('doklad_radek_entita');
            $table->string('order_number')->nullable(false)->change();
        });
    }
};
