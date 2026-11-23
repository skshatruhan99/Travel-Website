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
        Schema::create('flights', function (Blueprint $table) {
    $table->id();

    $table->string('airline')->nullable();
    $table->string('flight_number')->nullable();

    $table->string('departure_city');
    $table->string('arrival_city');

    $table->dateTime('departure_at');
    $table->dateTime('arrival_at');

    $table->decimal('price', 12, 2);

    $table->unsignedInteger('available_seats')->default(0);

    $table->string('class')->default('Economy');

    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
