@extends('layouts.app')

@section('title', 'Passenger Details - Flight Booking')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Passenger Details</h1>
        <p>{{ $flight->departure_city }} to {{ $flight->arrival_city }} · {{ $flight->departure_at->format('M j, Y') }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="detail-grid">
            <div class="booking-card">
                <h2>Passenger information</h2>
                @if($errors->any())<p class="badge badge-red" role="alert" style="margin:15px 0">{{ $errors->first() }}</p>@endif
                @guest
                    <p class="badge badge-red" style="margin:15px 0">
                        Please <a href="{{ route('login') }}" style="text-decoration:underline">login</a>
                        or <a href="{{ route('register') }}" style="text-decoration:underline">create an account</a>
                        before confirming your booking.
                    </p>
                @endguest
                <form action="{{ route('flight.booking.store', $flight) }}" method="POST">
                    @csrf
                    <input type="hidden" name="passenger_count" value="{{ $passengerCount }}">
                    @for($index = 0; $index < $passengerCount; $index++)
                        <fieldset class="booking-card" style="margin:15px 0">
                            <legend>Passenger {{ $index + 1 }}{{ $index === 0 ? ' · booking contact' : '' }}</legend>
                            <div class="form-grid">
                                <div class="form-group"><label for="passenger-{{ $index }}-first-name">First name (as on ID)</label><input id="passenger-{{ $index }}-first-name" name="passengers[{{ $index }}][first_name]" value="{{ old('passengers.'.$index.'.first_name') }}" class="form-control" required maxlength="100"></div>
                                <div class="form-group"><label for="passenger-{{ $index }}-last-name">Last name (as on ID)</label><input id="passenger-{{ $index }}-last-name" name="passengers[{{ $index }}][last_name]" value="{{ old('passengers.'.$index.'.last_name') }}" class="form-control" required maxlength="100"></div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group"><label for="passenger-{{ $index }}-dob">Date of birth</label><input id="passenger-{{ $index }}-dob" name="passengers[{{ $index }}][date_of_birth]" type="date" max="{{ today()->toDateString() }}" value="{{ old('passengers.'.$index.'.date_of_birth') }}" class="form-control"></div>
                                <div class="form-group"><label for="passenger-{{ $index }}-nationality">Nationality</label><input id="passenger-{{ $index }}-nationality" name="passengers[{{ $index }}][nationality]" value="{{ old('passengers.'.$index.'.nationality') }}" class="form-control" maxlength="100"></div>
                            </div>
                            <div class="form-group"><label for="passenger-{{ $index }}-passport">Passport / ID number</label><input id="passenger-{{ $index }}-passport" name="passengers[{{ $index }}][passport_number]" value="{{ old('passengers.'.$index.'.passport_number') }}" class="form-control" maxlength="50"></div>
                            @if($index === 0)
                                <div class="form-grid">
                                    <div class="form-group"><label for="contact-email">Contact email</label><input id="contact-email" name="passengers[0][email]" type="email" value="{{ old('passengers.0.email', auth()->user()?->email) }}" class="form-control" required maxlength="255"></div>
                                    <div class="form-group"><label for="contact-phone">Contact phone</label><input id="contact-phone" name="passengers[0][phone]" type="tel" value="{{ old('passengers.0.phone') }}" class="form-control" required maxlength="30"></div>
                                </div>
                            @endif
                        </fieldset>
                    @endfor
                    <div class="form-group"><label for="special_request">Special requests</label><textarea id="special_request" name="special_request" class="form-control" rows="3" maxlength="2000">{{ old('special_request') }}</textarea></div>
                    <button class="btn btn-primary" type="submit" style="width:100%">Continue to payment <i class="fa-solid fa-arrow-right"></i></button>
                </form>
            </div>
            <aside class="booking-card sticky-card">
                <span class="badge badge-red">Flight summary</span>
                <h3 style="margin-top:15px">{{ $flight->airline ?: 'Airline' }} · {{ $flight->flight_number }}</h3>
                <p>{{ $flight->departure_city }} to {{ $flight->arrival_city }}</p>
                <div class="tour-meta">
                    <span>Depart: {{ $flight->departure_at->format('M j, Y · H:i') }}</span>
                    <span>Arrive: {{ $flight->arrival_at->format('M j, Y · H:i') }}</span>
                    <span>{{ $flight->class }} · {{ $passengerCount }} passenger(s)</span>
                </div>
                <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">
                <div class="card-footer"><span>{{ $passengerCount }} × NPR {{ number_format($flight->price, 2) }}</span><strong>NPR {{ number_format($subtotal, 2) }}</strong></div>
                <div class="card-footer"><span>Taxes & fees</span><strong>NPR {{ number_format($tax, 2) }}</strong></div>
                <div class="invoice-total"><strong>Total</strong><strong class="price">NPR {{ number_format($total, 2) }}</strong></div>
            </aside>
        </div>
    </div>
</section>
@endsection
