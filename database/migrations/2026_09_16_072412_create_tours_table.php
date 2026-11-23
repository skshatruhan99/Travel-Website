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
        Schema::create('tours', function (Blueprint $table) {
    $table->id();

    $table->foreignId('destination_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('title');
    $table->string('slug')->unique();

    $table->text('short_description')->nullable();
    $table->longText('description')->nullable();

    $table->string('duration')->nullable();
    $table->string('grade')->nullable();

    $table->unsignedInteger('min_group_size')->default(1);
    $table->unsignedInteger('max_group_size')->nullable();

    $table->decimal('price', 12, 2);
    $table->decimal('child_price', 12, 2)->nullable();

    $table->string('transport')->nullable();
    $table->string('max_altitude')->nullable();

    $table->decimal('rating', 3, 2)->default(0);
    $table->unsignedInteger('review_count')->default(0);

    $table->string('featured_image')->nullable();

    $table->boolean('is_best_seller')->default(false);
    $table->boolean('is_published')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
