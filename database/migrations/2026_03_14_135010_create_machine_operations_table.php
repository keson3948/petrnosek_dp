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
        Schema::create('machine_operations', function (Blueprint $table) {
            $table->id();
            $table->string('machine_key');
            $table->string('operation_key');
            $table->string('operation_name')->nullable();
            $table->timestamps();

            $table->unique(['machine_key', 'operation_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_operations');
    }
};
