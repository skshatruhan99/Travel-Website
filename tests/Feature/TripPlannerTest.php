<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TripPlan;
use App\Models\TripPlanDay;
use App\Models\TripPlanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripPlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_planner_uses_database_destinations_and_saves_a_day_by_day_plan(): void
    {
        $destination = $this->destination();

        $this->get(route('planner'))
            ->assertOk()
            ->assertSee($destination->name)
            ->assertSee('Save trip and build itinerary');

        $user = User::factory()->create();
        $start = today()->addDays(10);
        $end = today()->addDays(12);
        $response = $this->actingAs($user)->post(route('planner.store'), [
            'title' => 'Pokhara Family Break',
            'destination_id' => $destination->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'adults' => 2,
            'children' => 1,
            'budget' => 50000,
        ]);
        $plan = TripPlan::firstOrFail();

        $this->assertDatabaseHas('trip_plans', [
            'id' => $plan->id,
            'user_id' => $user->id,
            'title' => 'Pokhara Family Break',
            'destination' => 'Pokhara',
            'adults' => 2,
            'children' => 1,
            'status' => 'saved',
        ]);
        $this->assertDatabaseCount('trip_plan_days', 3);
        $this->assertSame($start->toDateString(), $plan->days->first()->date->toDateString());
        $this->assertSame($end->toDateString(), $plan->days->last()->date->toDateString());
        $response->assertRedirect(route('planner.show', $plan));

        $this->actingAs($user)->get(route('planner.show', $plan))
            ->assertOk()
            ->assertSee('Pokhara Family Break')
            ->assertSee('Day 1')
            ->assertSee('Day 3');
    }

    public function test_user_can_add_catalog_items_edit_days_and_remove_itinerary_items(): void
    {
        [$user, $plan, $destination, $day] = $this->planFixture();
        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Pokhara Lakeside Tour',
            'slug' => 'planner-pokhara-tour',
            'price' => 1000,
            'child_price' => 500,
            'min_group_size' => 1,
            'max_group_size' => 10,
            'is_published' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'name' => 'Pokhara View Hotel',
            'slug' => 'planner-pokhara-hotel',
            'price_per_night' => 2000,
            'is_active' => true,
        ]);
        $activity = Activity::create([
            'destination_id' => $destination->id,
            'name' => 'Phewa Lake Boating',
            'slug' => 'planner-phewa-boating',
            'price' => 100,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('planner.show', $plan))
            ->assertOk()
            ->assertSee('Pokhara Lakeside Tour')
            ->assertSee('Pokhara View Hotel')
            ->assertSee('Phewa Lake Boating');

        $this->actingAs($user)->patch(route('planner.days.update', [$plan, $day]), [
            'location' => 'Lakeside',
            'description' => 'Visit Phewa Lake in the morning.',
        ])->assertRedirect();
        $this->assertDatabaseHas('trip_plan_days', [
            'id' => $day->id,
            'location' => 'Lakeside',
            'description' => 'Visit Phewa Lake in the morning.',
        ]);

        foreach ([
            ['destination', $destination->id, 0],
            ['tour', $tour->id, 2500],
            ['hotel', $hotel->id, 4000],
            ['activity', $activity->id, 300],
        ] as [$type, $id, $expectedPrice]) {
            $this->post(route('planner.items.store', [$plan, $day]), [
                'catalog_choice' => $type.':'.$id,
            ])->assertRedirect();

            $this->assertDatabaseHas('trip_plan_items', [
                'trip_plan_day_id' => $day->id,
                'item_type' => $type,
                'item_id' => $id,
                'price' => $expectedPrice,
            ]);
        }

        $plan->refresh();
        $this->assertEquals(6800, (float) $plan->estimated_total);
        $this->assertSame(4, $plan->items()->count());

        $orderedItems = $day->items()->orderBy('sort_order')->get();
        $this->patch(route('planner.items.move', [$plan, $orderedItems[1]]), [
            'direction' => 'up',
        ])->assertRedirect();
        $this->assertSame(
            [$orderedItems[1]->id, $orderedItems[0]->id],
            $day->items()->orderBy('sort_order')->limit(2)->pluck('id')->all()
        );

        $itemToRemove = TripPlanItem::where('trip_plan_day_id', $day->id)
            ->where('item_type', 'activity')
            ->firstOrFail();
        $this->delete(route('planner.items.destroy', [$plan, $itemToRemove]))->assertRedirect();
        $this->assertDatabaseMissing('trip_plan_items', ['id' => $itemToRemove->id]);
        $this->assertEquals(6500, (float) $plan->fresh()->estimated_total);
    }

    public function test_trip_plans_and_days_are_private_to_the_owner(): void
    {
        [$owner, $plan, , $day] = $this->planFixture();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->get(route('planner.show', $plan))->assertForbidden();
        $this->actingAs($otherUser)->patch(route('planner.days.update', [$plan, $day]), [
            'location' => 'Changed',
        ])->assertForbidden();
        $this->actingAs($otherUser)->delete(route('planner.destroy', $plan))->assertForbidden();
        $this->assertDatabaseHas('trip_plans', ['id' => $plan->id, 'user_id' => $owner->id]);
    }

    public function test_planner_validates_date_range_and_limits_plan_length(): void
    {
        $user = User::factory()->create();
        $destination = $this->destination();

        $this->actingAs($user)->post(route('planner.store'), [
            'destination_id' => $destination->id,
            'start_date' => today()->addDays(10)->toDateString(),
            'end_date' => today()->addDays(50)->toDateString(),
            'adults' => 1,
        ])->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('trip_plans', 0);
    }

    private function planFixture(): array
    {
        $user = User::factory()->create();
        $destination = $this->destination();
        $plan = TripPlan::create([
            'user_id' => $user->id,
            'title' => 'Pokhara Plan',
            'destination' => $destination->name,
            'start_date' => today()->addDays(10)->toDateString(),
            'end_date' => today()->addDays(12)->toDateString(),
            'adults' => 2,
            'children' => 1,
            'status' => 'saved',
        ]);
        $day = TripPlanDay::create([
            'trip_plan_id' => $plan->id,
            'day_number' => 1,
            'date' => $plan->start_date,
            'location' => $destination->name,
        ]);

        return [$user, $plan, $destination, $day];
    }

    private function destination(): Destination
    {
        return Destination::create([
            'name' => 'Pokhara',
            'slug' => 'planner-pokhara',
            'country' => 'Nepal',
            'is_active' => true,
        ]);
    }
}
