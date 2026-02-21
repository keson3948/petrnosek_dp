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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Srozumitelný název (např. "Expedice 1")
            $table->string('system_name'); // Název v CUPS (např. "Brother_QL_820NWB")
            $table->string('ip_address')->nullable(); // Pro budoucí použití / info

            // Výchozí nastavení pro tuto tiskárnu
            $table->string('page_size')->default('29x90mm'); // 62mm, 29x90mm atd.
            $table->string('media_type')->default('Labels');
            $table->string('orientation')->default('4'); // 3=Portrait, 4=Landscape (obvykle)

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
