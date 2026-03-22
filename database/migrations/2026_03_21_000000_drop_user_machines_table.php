<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_machines');
    }

    public function down(): void
    {
        Schema::create('user_machines', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('machine_key');
            $table->string('machine_name')->nullable();
            $table->timestamps();
        });
    }
};
