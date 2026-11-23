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
       Schema::create('bookings', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete();

    $table->foreignId('tour_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete();

    $table->string('booking_reference')->unique();

    $table->date('travel_start_date')->nullable();
    $table->date('travel_end_date')->nullable();

    $table->unsignedInteger('adults')->default(1);
    $table->unsignedInteger('children')->default(0);

    $table->decimal('subtotal', 12, 2)->default(0);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('tax', 12, 2)->default(0);
    $table->decimal('total_amount', 12, 2)->default(0);

    $table->string('currency')->default('INR');

    $table->enum('status', [
        'draft',
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'refunded'
    ])->default('pending');

    $table->text('special_request')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
