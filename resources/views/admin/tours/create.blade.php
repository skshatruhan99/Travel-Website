@extends('admin.layout')

@section('title', 'Add Tour | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
	<div class="admin-page-heading"><div><span class="admin-eyebrow">TRIP INVENTORY</span><h1>Add tour</h1><p>Newly published tours appear on the public Tours page.</p></div></div>
	<section class="admin-panel admin-form-panel">
		@include('admin.tours.form', ['tour' => null])
	</section>
</section>
@endsection
