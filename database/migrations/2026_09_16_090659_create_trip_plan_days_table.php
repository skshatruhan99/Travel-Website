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
        Schema::create('trip_plan_days', function (Blueprint $table) {
    $table->id();

    $table->foreignId('trip_plan_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->unsignedInteger('day_number');

    $table->date('date')->nullable();

    $table->string('location')->nullable();

    $table->text('description')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_plan_days');
    }
};
