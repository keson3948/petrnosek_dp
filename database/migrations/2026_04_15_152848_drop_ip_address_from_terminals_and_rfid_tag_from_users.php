<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terminals', function (Blueprint $table) {
            $table->dropUnique(['ip_address']);
            $table->dropColumn('ip_address');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['rfid_tag']);
            $table->dropColumn('rfid_tag');
        });
    }

    public function down(): void
    {
        //
    }
};
