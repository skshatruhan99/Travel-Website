@extends('admin.layout')

@section('title', 'Edit Coupon | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div>
            <span class="admin-eyebrow">PROMOTIONS</span>
            <h1>Edit coupon</h1>
            <p>Update the discount code details and availability.</p>
        </div>
    </div>

    <section class="admin-panel admin-form-panel">
        @include('admin.coupons.form', ['coupon' => $coupon])
    </section>
</section>
@endsection
