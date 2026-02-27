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
        Schema::create('production_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('machine_id')->nullable();
            $table->string('order_number');
            $table->string('drawing_number')->nullable();
            $table->string('operation_id');
            $table->integer('processed_quantity')->default(0);
            $table->string('status')->default('in_progress'); // in_progress, paused, completed
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('total_paused_seconds')->default(0);
            $table->timestamp('last_paused_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_records');
    }
};
