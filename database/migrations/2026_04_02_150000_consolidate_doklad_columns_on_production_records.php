<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->string('SysPrimKlicDokladu')->nullable()->after('ev_podsestav_id');
        });

        // Copy existing doklad_id values (these already contain SysPrimKlicDokladu)
        DB::table('production_records')
            ->whereNotNull('doklad_id')
            ->whereNull('SysPrimKlicDokladu')
            ->update(['SysPrimKlicDokladu' => DB::raw('doklad_id')]);

        Schema::table('production_records', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'vp_number', 'doklad_id']);
        });
    }

    public function down(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('machine_id');
            $table->string('vp_number')->nullable()->after('order_number');
            $table->string('doklad_id')->nullable()->after('ev_podsestav_id');
        });

        DB::table('production_records')
            ->whereNotNull('SysPrimKlicDokladu')
            ->update([
                'doklad_id' => DB::raw('"SysPrimKlicDokladu"'),
            ]);

        Schema::table('production_records', function (Blueprint $table) {
            $table->dropColumn('SysPrimKlicDokladu');
        });
    }
};
