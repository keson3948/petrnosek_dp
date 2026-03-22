<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('machine_operations');
        Schema::dropIfExists('areas');
    }

    public function down(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('machine_operations', function (Blueprint $table) {
            $table->id();
            $table->string('machine_key');
            $table->string('operation_key');
            $table->string('operation_name')->nullable();
            $table->timestamps();
            $table->unique(['machine_key', 'operation_key']);
        });
    }
};
