<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Activity;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class ProductionDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinations = [
            [
                'name' => 'Kathmandu',
                'slug' => 'kathmandu',
                'country' => 'Nepal',
                'location' => 'Central Nepal',
                'short_description' => 'Historic temples, Durbar Square, and the lively streets of Nepal’s capital.',
                'description' => 'Kathmandu is the cultural heart of Nepal, blending ancient heritage, colorful markets, and unforgettable mountain views.',
                'hero_image' => $this->photo('kathmandu-durbar-square'),
                'nearby_places' => [
                    ['name' => 'Kathmandu Durbar Square', 'distance' => 'Central Kathmandu', 'image' => $this->photo('kathmandu-durbar-square'), 'description' => 'A historic royal square in the heart of Kathmandu.'],
                    ['name' => 'Boudhanath', 'distance' => 'About 6 km', 'image' => $this->photo('boudhanath'), 'description' => 'One of the largest Buddhist stupas in Nepal.'],
                    ['name' => 'Swayambhunath', 'distance' => 'About 3 km', 'image' => $this->photo('swayambhunath'), 'description' => 'An ancient hilltop religious complex overlooking the valley.'],
                    ['name' => 'Thamel', 'distance' => 'Central Kathmandu', 'image' => $this->photo('thamel-kathmandu'), 'description' => 'A well-known central neighbourhood in Kathmandu.'],
                ],
                'best_time' => 'March to May',
                'weather' => 'Mild and pleasant',
                'currency' => 'NPR',
                'languages' => 'Nepali, English',
                'latitude' => 27.7172,
                'longitude' => 85.3240,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Pokhara',
                'slug' => 'pokhara',
                'country' => 'Nepal',
                'location' => 'Western Nepal',
                'short_description' => 'Lakeside serenity and panoramic Himalayan views.',
                'description' => 'Pokhara offers a relaxed lakeside atmosphere, adventure sports, and one of the best sunrise views in the world.',
                'hero_image' => $this->photo('phewa-lake'),
                'nearby_places' => [
                    ['name' => 'Phewa Lake', 'distance' => 'Central Pokhara', 'image' => $this->photo('phewa-lake'), 'description' => 'A lakeside landmark in Pokhara with views towards the Annapurna range.'],
                    ['name' => 'Pokhara Shanti Stupa', 'distance' => 'About 7 km', 'image' => $this->photo('pokhara-shanti-stupa'), 'description' => 'A hilltop Buddhist monument above Phewa Lake.'],
                    ['name' => 'Davis Falls', 'distance' => 'About 3 km', 'image' => $this->photo('davis-falls'), 'description' => 'A waterfall in Pokhara, also known locally as Devi’s Fall.'],
                    ['name' => 'Sarangkot', 'distance' => 'About 12 km', 'image' => $this->photo('sarangkot-view'), 'description' => 'A Pokhara viewpoint known for Himalayan panoramas and paragliding.'],
                    ['name' => 'International Mountain Museum', 'distance' => 'About 4 km', 'image' => $this->photo('international-mountain-museum'), 'description' => 'A museum in Pokhara dedicated to the mountains and mountaineering.'],
                    ['name' => 'Begnas Lake', 'distance' => 'About 15 km', 'image' => $this->photo('begnas-lake'), 'description' => 'A freshwater lake in the south-east of the Pokhara Valley.'],
                ],
                'best_time' => 'September to November',
                'weather' => 'Warm and clear',
                'currency' => 'NPR',
                'languages' => 'Nepali, English',
                'latitude' => 28.2096,
                'longitude' => 83.9856,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Annapurna',
                'slug' => 'annapurna',
                'country' => 'Nepal',
                'location' => 'Himalayan region',
                'short_description' => 'A legendary Himalayan circuit made for trekkers.',
                'description' => 'The Annapurna region is famous for breathtaking mountain scenery, classic trekking routes, and rich local culture.',
                'hero_image' => $this->photo('annapurna-base-camp'),
                'best_time' => 'October to December',
                'weather' => 'Cool climate',
                'currency' => 'NPR',
                'languages' => 'Nepali, English',
                'latitude' => 28.5941,
                'longitude' => 83.8738,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Butwal',
                'slug' => 'butwal',
                'country' => 'Nepal',
                'location' => 'Lumbini Province, western Nepal',
                'short_description' => 'A real western Nepal city beside the Chure foothills.',
                'description' => 'Butwal is a sub-metropolitan city and regional centre in Rupandehi District, Lumbini Province.',
                'hero_image' => $this->photo('butwal'),
                'best_time' => 'October to March',
                'currency' => 'NPR',
                'languages' => 'Nepali, English',
                'latitude' => 27.7000,
                'longitude' => 83.4660,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Bhaktapur',
                'slug' => 'bhaktapur',
                'country' => 'Nepal',
                'location' => 'Kathmandu Valley, Bagmati Province',
                'short_description' => 'A historic Newar city known for its Durbar Square.',
                'description' => 'Bhaktapur is a historic city in the Kathmandu Valley, home to Bhaktapur Durbar Square.',
                'hero_image' => $this->photo('bhaktapur-durbar-square'),
                'best_time' => 'October to April',
                'currency' => 'NPR',
                'languages' => 'Nepali, Nepal Bhasa',
                'latitude' => 27.6720,
                'longitude' => 85.4281,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Chitwan',
                'slug' => 'chitwan',
                'country' => 'Nepal',
                'location' => 'Bagmati Province, southern Nepal',
                'short_description' => 'Gateway to Chitwan National Park and its Terai landscape.',
                'description' => 'Chitwan is known for Chitwan National Park, Nepal’s first national park and a UNESCO World Heritage Site.',
                'hero_image' => $this->photo('chitwan-national-park'),
                'best_time' => 'October to March',
                'currency' => 'NPR',
                'languages' => 'Nepali, Tharu',
                'latitude' => 27.5000,
                'longitude' => 84.3333,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Lumbini',
                'slug' => 'lumbini',
                'country' => 'Nepal',
                'location' => 'Rupandehi District, Lumbini Province',
                'short_description' => 'A UNESCO World Heritage Site and Buddhist pilgrimage destination.',
                'description' => 'Lumbini is the historic birthplace of Siddhartha Gautama, the Buddha, in southern Nepal.',
                'hero_image' => $this->photo('lumbini-maya-devi-temple'),
                'best_time' => 'October to March',
                'currency' => 'NPR',
                'languages' => 'Nepali, English',
                'latitude' => 27.4833,
                'longitude' => 83.2767,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Bandipur',
                'slug' => 'bandipur',
                'country' => 'Nepal',
                'location' => 'Tanahun District, Gandaki Province',
                'short_description' => 'A historic hilltop settlement in central Nepal.',
                'description' => 'Bandipur is a hilltop settlement in Tanahun District with a preserved bazaar and views of the surrounding hills.',
                'hero_image' => $this->photo('old-inn-bandipur'),
                'best_time' => 'October to May',
                'currency' => 'NPR',
                'languages' => 'Nepali, Newar',
                'latitude' => 27.9344,
                'longitude' => 84.4064,
                'is_featured' => true,
                'is_active' => true,
            ],
        ];

        foreach ($destinations as $destination) {
            Destination::updateOrCreate(
                ['slug' => $destination['slug']],
                $destination
            );
        }

        $kathmandu = Destination::where('slug', 'kathmandu')->firstOrFail();
        $pokhara = Destination::where('slug', 'pokhara')->firstOrFail();
        $annapurna = Destination::where('slug', 'annapurna')->firstOrFail();
        $butwal = Destination::where('slug', 'butwal')->firstOrFail();
        $bandipur = Destination::where('slug', 'bandipur')->firstOrFail();

        $tours = [
            [
                'destination_id' => $butwal->id,
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
                'featured_image' => $this->photo('butwal'),
                'is_best_seller' => false,
                'is_published' => true,
            ],
            [
                'destination_id' => $kathmandu->id,
                'title' => 'Kathmandu Heritage Tour',
                'slug' => 'kathmandu-heritage-tour',
                'short_description' => 'Explore UNESCO heritage, spiritual temples, and local culture.',
                'description' => 'Walk through the old streets of Kathmandu, visit iconic temples, and enjoy a rich culture experience in the capital city.',
                'duration' => '4 days',
                'grade' => 'Easy',
                'min_group_size' => 1,
                'max_group_size' => 12,
                'price' => 2000,
                'child_price' => 1000,
                'transport' => 'Private vehicle',
                'max_altitude' => '1,400 m',
                'rating' => 4.8,
                'review_count' => 96,
                'featured_image' => $this->photo('kathmandu-durbar-square'),
                'is_best_seller' => true,
                'is_published' => true,
            ],
            [
                'destination_id' => $pokhara->id,
                'title' => 'Pokhara Lake & Adventure',
                'slug' => 'pokhara-lake-adventure',
                'short_description' => 'A lakeside getaway with sunrise views and gentle adventure.',
                'description' => 'Enjoy sunrise at Sarangkot, boating on Phewa Lake, and guided nature walks in one of Nepal’s most scenic cities.',
                'duration' => '5 days',
                'grade' => 'Moderate',
                'min_group_size' => 1,
                'max_group_size' => 10,
                'price' => 2000,
                'child_price' => 1000,
                'transport' => 'Private van',
                'max_altitude' => '1,600 m',
                'rating' => 4.7,
                'review_count' => 112,
                'featured_image' => $this->photo('phewa-lake'),
                'is_best_seller' => true,
                'is_published' => true,
            ],
            [
                'destination_id' => $pokhara->id,
                'title' => 'Pokhara Sunrise Tour',
                'slug' => 'pokhara-sunrise-tour',
                'short_description' => 'Catch first light over the Himalayas from Sarangkot.',
                'description' => 'Travel to Sarangkot before dawn for a guided sunrise over the Annapurna range, followed by breakfast and a relaxed return to Lakeside.',
                'duration' => '1 day',
                'grade' => 'Easy',
                'min_group_size' => 1,
                'max_group_size' => 10,
                'price' => 1000,
                'child_price' => 500,
                'transport' => 'Private vehicle',
                'max_altitude' => '1,600 m',
                'rating' => 4.8,
                'review_count' => 74,
                'featured_image' => $this->photo('sarangkot-sunrise'),
                'is_best_seller' => false,
                'is_published' => true,
            ],
            [
                'destination_id' => $pokhara->id,
                'title' => 'Pokhara City Tour',
                'slug' => 'pokhara-city-tour',
                'short_description' => 'Visit Pokhara’s lakeside, caves, falls, and local landmarks.',
                'description' => 'Discover the highlights of Pokhara with a local guide, including Davis Falls, the International Mountain Museum, and time beside Phewa Lake.',
                'duration' => '1 day',
                'grade' => 'Easy',
                'min_group_size' => 1,
                'max_group_size' => 12,
                'price' => 1000,
                'child_price' => 500,
                'transport' => 'Private van',
                'max_altitude' => '900 m',
                'rating' => 4.7,
                'review_count' => 59,
                'featured_image' => $this->photo('international-mountain-museum'),
                'is_best_seller' => false,
                'is_published' => true,
            ],
            [
                'destination_id' => $pokhara->id,
                'title' => 'Pokhara Adventure Package',
                'slug' => 'pokhara-adventure-package',
                'short_description' => 'Pair lake time with mountain viewpoints and guided outdoor adventures.',
                'description' => 'Spend several days exploring Pokhara with a Sarangkot sunrise, boating on Phewa Lake, and a choice of guided outdoor activities.',
                'duration' => '4 days',
                'grade' => 'Moderate',
                'min_group_size' => 1,
                'max_group_size' => 8,
                'price' => 2000,
                'child_price' => 1000,
                'transport' => 'Private vehicle',
                'max_altitude' => '1,600 m',
                'rating' => 4.9,
                'review_count' => 42,
                'featured_image' => $this->photo('pokhara-shanti-stupa'),
                'is_best_seller' => true,
                'is_published' => true,
            ],
            [
                'destination_id' => $annapurna->id,
                'title' => 'Annapurna Classic Trek',
                'slug' => 'annapurna-classic-trek',
                'short_description' => 'A classic Himalayan trek with great views and local culture.',
                'description' => 'Trek through lush valleys, traditional villages, and spectacular mountain scenery on the Annapurna circuit.',
                'duration' => '7 days',
                'grade' => 'Moderate to Challenging',
                'min_group_size' => 2,
                'max_group_size' => 12,
                'price' => 2000,
                'child_price' => 1000,
                'transport' => 'Jeep + trek',
                'max_altitude' => '2,540 m',
                'rating' => 4.9,
                'review_count' => 154,
                'featured_image' => $this->photo('annapurna-base-camp'),
                'is_best_seller' => true,
                'is_published' => true,
            ],
        ];

        foreach ($tours as $tour) {
            $savedTour = Tour::updateOrCreate(
                ['slug' => $tour['slug']],
                $tour
            );

            if ($savedTour->is_published && ! $savedTour->dates()->whereDate('departure_date', '>=', today())->exists()) {
                $savedTour->dates()->create([
                    'departure_date' => today()->addDays(30)->toDateString(),
                    'capacity' => 10,
                    'is_available' => true,
                ]);
            }
        }

        Flight::updateOrCreate(
            ['flight_number' => 'RA101', 'departure_at' => now()->addDays(14)->setTime(8, 0)],
            [
                'airline' => 'Nepal Airlines',
                'arrival_city' => 'Pokhara',
                'departure_city' => 'Kathmandu',
                'arrival_at' => now()->addDays(14)->setTime(8, 45),
                'price' => 2000,
                'available_seats' => 20,
                'class' => 'Economy',
                'is_active' => true,
            ],
        );

        $hotels = [
            [
                'destination_id' => $kathmandu->id,
                'name' => 'Hotel Yak & Yeti',
                'slug' => 'himalayan-courtyard',
                'short_description' => 'Boutique stay in the heart of Kathmandu.',
                'description' => 'A peaceful boutique hotel with modern comforts, temple views, and a welcoming staff.',
                'address' => 'Durbar Marg, Kathmandu, Nepal',
                'stars' => 4,
                'price_per_night' => 1000,
                'rating' => 4.6,
                'review_count' => 88,
                'featured_image' => $this->photo('hotel-yak-yeti'),
                'free_cancellation' => true,
                'breakfast_included' => true,
                'mountain_view' => false,
                'pool' => false,
                'spa' => true,
                'is_active' => true,
            ],
            [
                'destination_id' => $pokhara->id,
                'name' => 'Hotel Barahi',
                'slug' => 'lakeview-retreat',
                'short_description' => 'Lakeside comfort and scenic mountain sunrise.',
                'description' => 'A refined retreat located by the lake with easy access to local adventure activities and relaxing evenings.',
                'address' => 'Lakeside Road, Pokhara, Nepal',
                'stars' => 5,
                'price_per_night' => 2000,
                'rating' => 4.9,
                'review_count' => 125,
                'featured_image' => $this->photo('hotel-barahi'),
                'free_cancellation' => true,
                'breakfast_included' => true,
                'mountain_view' => true,
                'pool' => true,
                'spa' => true,
                'is_active' => true,
            ],
            [
                'destination_id' => $bandipur->id,
                'name' => 'The Old Inn, Bandipur',
                'slug' => 'trail-ridge-lodge',
                'short_description' => 'A historic inn in Bandipur’s old bazaar.',
                'description' => 'The Old Inn is a heritage hotel in Bandipur, Nepal.',
                'address' => 'Bandipur Bazaar, Tanahun, Nepal',
                'stars' => 4,
                'price_per_night' => 1000,
                'rating' => 4.7,
                'review_count' => 74,
                'featured_image' => $this->photo('old-inn-bandipur'),
                'free_cancellation' => false,
                'breakfast_included' => true,
                'mountain_view' => true,
                'pool' => false,
                'spa' => false,
                'is_active' => true,
            ],
        ];

        foreach ($hotels as $hotel) {
            $savedHotel = Hotel::updateOrCreate(
                ['slug' => $hotel['slug']],
                $hotel
            );
            $savedHotel->rooms()->updateOrCreate(
                ['name' => 'Standard Room'],
                [
                    'description' => 'Comfortable room for up to two guests.',
                    'price_per_night' => $savedHotel->price_per_night,
                    'max_guests' => 2,
                    'rooms_available' => 1,
                    'includes_breakfast' => $savedHotel->breakfast_included,
                    'is_active' => true,
                ]
            );
        }

        $activities = [
            [
                'destination_id' => $pokhara->id,
                'name' => 'Mountain Hiking',
                'slug' => 'mountain-hiking',
                'description' => 'Explore beautiful Himalayan trails with a local guide.',
                'category' => 'Hiking',
                'duration' => '4 hours',
                'difficulty' => 'Moderate',
                'price' => 500,
                'image' => $this->photo('sarangkot-view'),
                'capacity' => 12,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'destination_id' => $pokhara->id,
                'name' => 'Lake Boating',
                'slug' => 'lake-boating',
                'description' => 'Relax and enjoy peaceful lake views on Phewa Lake.',
                'category' => 'Water activity',
                'duration' => '2 hours',
                'difficulty' => 'Easy',
                'price' => 1000,
                'image' => $this->photo('phewa-lake'),
                'capacity' => 20,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'destination_id' => $pokhara->id,
                'name' => 'Paragliding',
                'slug' => 'paragliding',
                'description' => 'Experience Nepal from the sky with a certified tandem pilot.',
                'category' => 'Adventure',
                'duration' => '30 minutes',
                'difficulty' => 'Easy',
                'price' => 2000,
                'image' => $this->photo('paragliding-pokhara'),
                'capacity' => 10,
                'is_featured' => true,
                'is_active' => true,
            ],
        ];

        foreach ($activities as $activity) {
            Activity::updateOrCreate(
                ['slug' => $activity['slug']],
                $activity
            );
        }
    }

    private function photo(string $key): string
    {
        return config('nepal_photos.photos.'.$key.'.path');
    }
}
