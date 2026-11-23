@extends('layouts.app')

@section('title', $plan->title.' | Trip Planner')

@section('content')
<section class="page-hero">
    <div class="container">
        <a href="{{ route('planner') }}" class="text-link"><i class="fa-solid fa-arrow-left"></i> All trips</a>
        <h1 style="margin-top:12px">{{ $plan->title }}</h1>
        <p>{{ $plan->destination }} · {{ $plan->start_date->format('d M Y') }} – {{ $plan->end_date->format('d M Y') }} · {{ $plan->adults + $plan->children }} {{ \Illuminate\Support\Str::plural('traveler', $plan->adults + $plan->children) }}</p>
    </div>
</section>

<section class="section bg-light">
    <div class="container">
        @if(session('success'))<p class="badge badge-green" role="status" style="margin-bottom:20px">{{ session('success') }}</p>@endif
        @if($errors->any())<p class="badge badge-red" role="alert" style="margin-bottom:20px">{{ $errors->first() }}</p>@endif
        <div class="planner-grid">
            <div class="planner-box">
                <h2>Itinerary</h2>
                <p class="muted" style="margin-bottom:20px">Add catalog items to a day, write notes, and remove items as your plans change.</p>
                @forelse($plan->days as $day)
                    <article class="booking-card" style="margin-bottom:18px">
                        <div class="section-heading">
                            <div><span class="badge badge-red">Day {{ $day->day_number }}</span><h3 style="margin-top:8px">{{ $day->date?->format('D, d M Y') }}</h3></div>
                        </div>
                        <form action="{{ route('planner.days.update', [$plan, $day]) }}" method="POST">
                            @csrf @method('PATCH')
                            <div class="form-group"><label for="location-{{ $day->id }}">Location</label><input id="location-{{ $day->id }}" name="location" value="{{ old('location', $day->location) }}" class="form-control" maxlength="255"></div>
                            <div class="form-group"><label for="description-{{ $day->id }}">Day notes</label><textarea id="description-{{ $day->id }}" name="description" rows="2" class="form-control" maxlength="2000">{{ old('description', $day->description) }}</textarea></div>
                            <button type="submit" class="btn btn-outline">Save day details</button>
                        </form>

                        @if($day->items->isNotEmpty())
                            <div style="margin-top:18px">
                                @foreach($day->items as $item)
                                    <div class="plan-item">
                                        <div class="plan-number">{{ $loop->iteration }}</div>
                                        <div style="flex:1">
                                            <strong>{{ $item->name }}</strong>
                                            <p class="muted">{{ ucfirst($item->item_type) }}@if($item->description) · {{ \Illuminate\Support\Str::limit($item->description, 110) }}@endif</p>
                                            <small class="muted">Estimate: NPR {{ number_format($item->price, 2) }}</small>
                                        </div>
                                        <div>
                                            @if(! $loop->first)
                                                <form action="{{ route('planner.items.move', [$plan, $item]) }}" method="POST" style="display:inline">
                                                    @csrf @method('PATCH')<input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="text-link" aria-label="Move {{ $item->name }} earlier">↑</button>
                                                </form>
                                            @endif
                                            @if(! $loop->last)
                                                <form action="{{ route('planner.items.move', [$plan, $item]) }}" method="POST" style="display:inline">
                                                    @csrf @method('PATCH')<input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="text-link" aria-label="Move {{ $item->name }} later">↓</button>
                                                </form>
                                            @endif
                                        </div>
                                        <form action="{{ route('planner.items.destroy', [$plan, $item]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-link" style="border:0;background:none;cursor:pointer">Remove</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="muted" style="margin:15px 0">No activities or places added to this day yet.</p>
                        @endif

                        <form action="{{ route('planner.items.store', [$plan, $day]) }}" method="POST" class="form-grid" style="margin-top:18px">
                            @csrf
                            <div class="form-group">
                                <label for="catalog-choice-{{ $day->id }}">Add a destination or experience</label>
                                <select id="catalog-choice-{{ $day->id }}" name="catalog_choice" class="form-control" required>
                                    <option value="">Select an item</option>
                                    @foreach(['destination' => 'Destinations', 'tour' => 'Tours', 'hotel' => 'Hotels', 'activity' => 'Activities'] as $type => $label)
                                        @if($choices[$type]->isNotEmpty())
                                            <optgroup label="{{ $label }}">
                                                @foreach($choices[$type] as $choice)
                                                    <option value="{{ $type }}:{{ $choice->id }}">{{ $choice->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="align-self:end"><button type="submit" class="btn btn-primary">Add to Day {{ $day->day_number }}</button></div>
                        </form>
                    </article>
                @empty
                    <p class="muted">This trip has no itinerary days.</p>
                @endforelse
            </div>

            <aside class="planner-box sticky-card">
                <span class="badge badge-red">Trip overview</span>
                <h2 style="margin-top:15px">{{ $plan->destination }}</h2>
                <p class="muted">{{ $plan->start_date->format('d M Y') }} – {{ $plan->end_date->format('d M Y') }}</p>
                <p class="muted" style="margin-top:8px">{{ $plan->adults }} adults · {{ $plan->children }} children</p>
                @if($plan->budget)<div class="card-footer" style="margin-top:18px"><span>Budget</span><strong>NPR {{ number_format($plan->budget, 2) }}</strong></div>@endif
                <div class="invoice-total"><strong>Current estimate</strong><strong class="price">NPR {{ number_format($plan->estimated_total, 2) }}</strong></div>
                <p class="muted" style="margin-top:12px">Estimates use listed item prices; hotel estimate assumes one room. This planner saves an itinerary, not reservations.</p>
                <form action="{{ route('planner.destroy', $plan) }}" method="POST" style="margin-top:20px" onsubmit="return confirm('Delete this trip plan and its itinerary?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline" style="width:100%">Delete trip plan</button>
                </form>
            </aside>
        </div>
    </div>
</section>
@endsection
