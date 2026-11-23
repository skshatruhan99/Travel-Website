<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyTourExists = DB::table('tours')->where('slug', 'pokhara')->exists();
        if (! $legacyTourExists) {
            return;
        }

        $butwalId = DB::table('destinations')->where('slug', 'butwal')->value('id');
        if (! $butwalId) {
            throw new LogicException('Cannot correct the legacy tour because the Butwal destination is missing.');
        }

        DB::table('tours')->where('slug', 'pokhara')->update([
            'destination_id' => $butwalId,
            'title' => 'Butwal City Tour',
            'slug' => 'butwal-city-tour',
            'short_description' => 'Explore Butwal, a lively city in western Nepal.',
            'description' => 'Discover Butwal’s city center, local markets, and its setting beside the Chure foothills with a local guide.',
            'duration' => '1 day',
            'grade' => 'Easy',
            'min_group_size' => 1,
            'max_group_size' => 10,
            'price' => 1000,
            'child_price' => 500,
            'transport' => 'Private vehicle',
            'max_altitude' => '300 m',
            'rating' => 4.5,
            'review_count' => 28,
            'featured_image' => config('nepal_photos.photos.butwal.path'),
            'is_best_seller' => false,
            'is_published' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        throw new LogicException('Correcting legacy Nepal catalog records cannot be safely reversed.');
    }
};
