@extends('admin.layout')

@section('title', 'Create Coupon | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div>
            <span class="admin-eyebrow">PROMOTIONS</span>
            <h1>Create coupon</h1>
            <p>Add a discount code for customer bookings.</p>
        </div>
    </div>

    <section class="admin-panel admin-form-panel">
        @include('admin.coupons.form', ['coupon' => null])
    </section>
</section>
@endsection
