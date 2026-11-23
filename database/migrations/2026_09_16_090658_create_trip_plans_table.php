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
        Schema::create('trip_plans', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete();

    $table->string('title');

    $table->string('destination')->nullable();

    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();

    $table->unsignedInteger('adults')->default(1);
    $table->unsignedInteger('children')->default(0);

    $table->decimal('budget', 12, 2)->nullable();
    $table->decimal('estimated_total', 12, 2)->default(0);

    $table->enum('status', [
        'draft',
        'saved',
        'booked'
    ])->default('draft');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_plans');
    }
};
