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
        Schema::create('hotels', function (Blueprint $table) {
    $table->id();

    $table->foreignId('destination_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('name');
    $table->string('slug')->unique();

    $table->text('short_description')->nullable();
    $table->longText('description')->nullable();

    $table->string('address')->nullable();

    $table->unsignedTinyInteger('stars')->nullable();

    $table->decimal('price_per_night', 12, 2);

    $table->decimal('rating', 3, 2)->default(0);
    $table->unsignedInteger('review_count')->default(0);

    $table->string('featured_image')->nullable();

    $table->boolean('free_cancellation')->default(false);
    $table->boolean('breakfast_included')->default(false);
    $table->boolean('mountain_view')->default(false);
    $table->boolean('pool')->default(false);
    $table->boolean('spa')->default(false);

    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
