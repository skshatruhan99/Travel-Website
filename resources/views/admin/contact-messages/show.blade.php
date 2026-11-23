@extends('admin.layout')

@section('title', 'Contact Message | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div>
            <a class="admin-text-link" href="{{ route('admin.contact-messages.index') }}"><i class="fa-solid fa-arrow-left"></i> All messages</a>
            <h1>Contact message #{{ $contactMessage->id }}</h1>
            <p>Received {{ $contactMessage->created_at->format('M j, Y \a\t g:i A') }}</p>
        </div>
        <span class="admin-status {{ $contactMessage->status === 'resolved' ? 'status-confirmed' : ($contactMessage->status === 'in_progress' ? 'status-pending' : '') }}">{{ str_replace('_', ' ', ucfirst($contactMessage->status)) }}</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">SENDER</span><h2>{{ $contactMessage->name }}</h2></div></div>
        <div class="admin-panel-body">
            <p><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></p>
            @if($contactMessage->phone)<p><a href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a></p>@endif
            @if($contactMessage->user)<p>Account: {{ $contactMessage->user->name }} ({{ $contactMessage->user->email }})</p>@endif
            <p>Category: {{ $contactMessage->category ?: 'General inquiry' }}</p>
            @if($contactMessage->booking_reference)<p>Booking reference: {{ $contactMessage->booking_reference }}</p>@endif
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">MESSAGE</span><h2>Customer message</h2></div></div>
        <div class="admin-panel-body"><p style="white-space:pre-wrap">{{ $contactMessage->message }}</p></div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">SUPPORT WORKFLOW</span><h2>Update status</h2></div></div>
        <div class="admin-panel-body">
            @if(session('success'))<p class="admin-form-errors" role="status">{{ session('success') }}</p>@endif
            @if($errors->any())<p class="admin-form-errors" role="alert">{{ $errors->first() }}</p>@endif
            <form action="{{ route('admin.contact-messages.status', $contactMessage) }}" method="POST" class="admin-status-form">
                @csrf @method('PATCH')
                <label for="message-status">Status</label>
                <select id="message-status" name="status" class="admin-status-select" required>
                    @foreach(['new' => 'New', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $status => $label)
                        <option value="{{ $status }}" @selected($contactMessage->status === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="admin-role-button" type="submit">Save status</button>
            </form>
        </div>
    </section>
</section>
@endsection
