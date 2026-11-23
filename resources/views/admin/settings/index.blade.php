@extends('admin.layout')

@section('title', 'Settings | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div>
            <span class="admin-eyebrow">SITE SETTINGS</span>
            <h1>Brand & contact details</h1>
            <p>Configure your website identity and customer support information.</p>
        </div>
    </div>

    <section class="admin-panel admin-form-panel">
        <form action="{{ route('admin.settings.store') }}" method="POST" class="admin-form">
            @csrf

            @if($errors->any())
                <div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>
            @endif

            <div class="admin-form-grid">
                <div class="form-group">
                    <label for="site_name">Site name</label>
                    <input id="site_name" name="site_name" class="form-control" value="{{ old('site_name', $settings['site_name'] ?? 'NpAdventure') }}">
                </div>

                <div class="form-group">
                    <label for="currency">Currency</label>
                    <input id="currency" name="currency" class="form-control" value="{{ old('currency', $settings['currency'] ?? 'NPR') }}">
                </div>

                <div class="form-group">
                    <label for="support_email">Support email</label>
                    <input id="support_email" name="support_email" type="email" class="form-control" value="{{ old('support_email', $settings['support_email'] ?? '') }}">
                </div>

                <div class="form-group">
                    <label for="support_phone">Support phone</label>
                    <input id="support_phone" name="support_phone" class="form-control" value="{{ old('support_phone', $settings['support_phone'] ?? '') }}">
                </div>

                <div class="form-group admin-form-wide">
                    <label for="address">Address</label>
                    <input id="address" name="address" class="form-control" value="{{ old('address', $settings['address'] ?? '') }}">
                </div>

                <div class="form-group admin-form-wide">
                    <label for="meta_title">Meta title</label>
                    <input id="meta_title" name="meta_title" class="form-control" value="{{ old('meta_title', $settings['meta_title'] ?? 'NpAdventure') }}">
                </div>

                <div class="form-group admin-form-wide">
                    <label for="meta_description">Meta description</label>
                    <textarea id="meta_description" name="meta_description" class="form-control" rows="4">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="admin-form-actions">
                <button class="admin-primary-action" type="submit">Save settings</button>
            </div>
        </form>
    </section>
</section>
@endsection
