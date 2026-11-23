<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_night', 12, 2);
            $table->unsignedTinyInteger('max_guests')->default(2);
            $table->unsignedSmallInteger('rooms_available')->default(1);
            $table->boolean('includes_breakfast')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['hotel_id', 'is_active']);
        });

        DB::table('hotels')->orderBy('id')->each(function (object $hotel): void {
            DB::table('hotel_rooms')->insert([
                'hotel_id' => $hotel->id,
                'name' => 'Standard Room',
                'description' => 'Comfortable room for up to two guests.',
                'price_per_night' => $hotel->price_per_night,
                'max_guests' => 2,
                'rooms_available' => 1,
                'includes_breakfast' => $hotel->breakfast_included,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};
