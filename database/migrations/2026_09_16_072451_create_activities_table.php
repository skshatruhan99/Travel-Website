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
        Schema::create('activities', function (Blueprint $table) {
    $table->id();

    $table->foreignId('destination_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('name');
    $table->string('slug')->unique();

    $table->text('description')->nullable();

    $table->string('category')->nullable();
    $table->string('duration')->nullable();
    $table->string('difficulty')->nullable();

    $table->decimal('price', 12, 2)->default(0);

    $table->string('image')->nullable();

    $table->boolean('is_featured')->default(false);
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
