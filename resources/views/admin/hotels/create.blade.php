@extends('admin.layout')

@section('title', 'Add Hotel | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">STAY INVENTORY</span><h1>Add hotel</h1><p>New active hotels appear on the public Hotels page.</p></div></div><section class="admin-panel admin-form-panel">@include('admin.hotels.form', ['hotel' => null])</section></section>
@endsection
