@extends('admin.layout')

@section('title', 'Edit Tour | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
	<div class="admin-page-heading"><div><span class="admin-eyebrow">TRIP INVENTORY</span><h1>Edit tour</h1><p>Update tour details and publication status.</p></div></div>
	<section class="admin-panel admin-form-panel">
		@include('admin.tours.form', ['tour' => $tour])
	</section>
</section>
@endsection
