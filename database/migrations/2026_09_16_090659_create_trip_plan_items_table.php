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
        Schema::create('trip_plan_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('trip_plan_day_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('item_type');

    $table->unsignedBigInteger('item_id')->nullable();

    $table->string('name');

    $table->decimal('price', 12, 2)->default(0);

    $table->text('description')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_plan_items');
    }
};
