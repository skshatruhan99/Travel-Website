<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('tour_date_id')
                ->nullable()
                ->after('tour_id')
                ->constrained('tour_dates')
                ->nullOnDelete();
            $table->timestamp('seat_hold_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tour_date_id');
            $table->dropColumn('seat_hold_expires_at');
        });
    }
};
