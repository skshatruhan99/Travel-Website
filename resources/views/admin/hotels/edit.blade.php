@extends('admin.layout')

@section('title', 'Edit Hotel | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">STAY INVENTORY</span><h1>Edit hotel</h1><p>Update listing details and amenities.</p></div></div><section class="admin-panel admin-form-panel">@include('admin.hotels.form', ['hotel' => $hotel])</section></section>
@endsection
