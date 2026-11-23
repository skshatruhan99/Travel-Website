<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $photoPath = static fn (string $key): string => config('nepal_photos.photos.'.$key.'.path');

        $bandipur = [
            'name' => 'Bandipur',
            'slug' => 'bandipur',
            'country' => 'Nepal',
            'location' => 'Tanahun District, Gandaki Province',
            'short_description' => 'A historic hilltop settlement in central Nepal.',
            'description' => 'Bandipur is a hilltop settlement in Tanahun District with a preserved bazaar and views of the surrounding hills.',
            'hero_image' => $photoPath('old-inn-bandipur'),
            'best_time' => 'October to May',
            'currency' => 'NPR',
            'languages' => 'Nepali, Newar',
            'latitude' => 27.9344,
            'longitude' => 84.4064,
            'is_featured' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $bandipurExists = DB::table('destinations')->where('slug', 'bandipur')->exists();
        if (! $bandipurExists) {
            DB::table('destinations')->insert($bandipur);
        }

        $destinationPhotos = [
            'kathmandu' => 'kathmandu-durbar-square',
            'pokhara' => 'phewa-lake',
            'butwal' => 'butwal',
            'annapurna' => 'annapurna-base-camp',
            'bhaktapur' => 'bhaktapur-durbar-square',
            'chitwan' => 'chitwan-national-park',
            'lumbini' => 'lumbini-maya-devi-temple',
            'bandipur' => 'old-inn-bandipur',
        ];

        foreach ($destinationPhotos as $slug => $photo) {
            DB::table('destinations')
                ->where('slug', $slug)
                ->update(['hero_image' => $photoPath($photo), 'updated_at' => now()]);
        }

        $pokharaPlaces = [
            ['name' => 'Phewa Lake', 'distance' => 'Central Pokhara', 'image' => $photoPath('phewa-lake'), 'description' => 'A lakeside landmark in Pokhara with views towards the Annapurna range.'],
            ['name' => 'Pokhara Shanti Stupa', 'distance' => 'About 7 km', 'image' => $photoPath('pokhara-shanti-stupa'), 'description' => 'A hilltop Buddhist monument above Phewa Lake.'],
            ['name' => 'Davis Falls', 'distance' => 'About 3 km', 'image' => $photoPath('davis-falls'), 'description' => 'A waterfall in Pokhara, also known locally as Devi’s Fall.'],
            ['name' => 'Sarangkot', 'distance' => 'About 12 km', 'image' => $photoPath('sarangkot-view'), 'description' => 'A Pokhara viewpoint known for Himalayan panoramas and paragliding.'],
            ['name' => 'International Mountain Museum', 'distance' => 'About 4 km', 'image' => $photoPath('international-mountain-museum'), 'description' => 'A museum in Pokhara dedicated to the mountains and mountaineering.'],
            ['name' => 'Begnas Lake', 'distance' => 'About 15 km', 'image' => $photoPath('begnas-lake'), 'description' => 'A freshwater lake in the south-east of the Pokhara Valley.'],
        ];

        DB::table('destinations')->where('slug', 'pokhara')->update([
            'nearby_places' => json_encode($pokharaPlaces, JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);

        $kathmanduPlaces = [
            ['name' => 'Kathmandu Durbar Square', 'distance' => 'Central Kathmandu', 'image' => $photoPath('kathmandu-durbar-square'), 'description' => 'A historic royal square in the heart of Kathmandu.'],
            ['name' => 'Boudhanath', 'distance' => 'About 6 km', 'image' => $photoPath('boudhanath'), 'description' => 'One of the largest Buddhist stupas in Nepal.'],
            ['name' => 'Swayambhunath', 'distance' => 'About 3 km', 'image' => $photoPath('swayambhunath'), 'description' => 'An ancient hilltop religious complex overlooking the valley.'],
            ['name' => 'Thamel', 'distance' => 'Central Kathmandu', 'image' => $photoPath('thamel-kathmandu'), 'description' => 'A well-known central neighbourhood in Kathmandu.'],
        ];

        DB::table('destinations')->where('slug', 'kathmandu')->update([
            'nearby_places' => json_encode($kathmanduPlaces, JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);

        $tourPhotos = [
            'kathmandu-heritage-tour' => 'kathmandu-durbar-square',
            'pokhara-lake-adventure' => 'phewa-lake',
            'pokhara-sunrise-tour' => 'sarangkot-sunrise',
            'pokhara-city-tour' => 'international-mountain-museum',
            'pokhara-adventure-package' => 'pokhara-shanti-stupa',
            'annapurna-classic-trek' => 'annapurna-base-camp',
        ];

        foreach ($tourPhotos as $slug => $photo) {
            DB::table('tours')
                ->where('slug', $slug)
                ->update(['featured_image' => $photoPath($photo), 'updated_at' => now()]);
        }

        $hotelPhotos = [
            ['slug' => 'himalayan-courtyard', 'name' => 'Hotel Yak & Yeti', 'address' => 'Durbar Marg, Kathmandu, Nepal', 'photo' => 'hotel-yak-yeti'],
            ['slug' => 'lakeview-retreat', 'name' => 'Hotel Barahi', 'address' => 'Lakeside, Pokhara, Nepal', 'photo' => 'hotel-barahi'],
            ['slug' => 'trail-ridge-lodge', 'name' => 'The Old Inn, Bandipur', 'address' => 'Bandipur Bazaar, Tanahun, Nepal', 'photo' => 'old-inn-bandipur'],
        ];

        foreach ($hotelPhotos as $hotel) {
            DB::table('hotels')
                ->where('slug', $hotel['slug'])
                ->update([
                    'name' => $hotel['name'],
                    'address' => $hotel['address'],
                    'featured_image' => $photoPath($hotel['photo']),
                    'updated_at' => now(),
                ]);
        }

        $bandipurId = DB::table('destinations')->where('slug', 'bandipur')->value('id');
        DB::table('hotels')->where('slug', 'trail-ridge-lodge')->update([
            'destination_id' => $bandipurId,
            'short_description' => 'A historic inn in Bandipur’s old bazaar.',
            'description' => 'The Old Inn is a heritage hotel in Bandipur, Nepal.',
            'updated_at' => now(),
        ]);

        $activityPhotos = [
            'mountain-hiking' => 'sarangkot-view',
            'lake-boating' => 'phewa-lake',
            'paragliding' => 'paragliding-pokhara',
        ];

        foreach ($activityPhotos as $slug => $photo) {
            DB::table('activities')
                ->where('slug', $slug)
                ->update(['image' => $photoPath($photo), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        throw new LogicException('Nepal photo curation replaces unverified image data and cannot be reversed safely.');
    }
};
