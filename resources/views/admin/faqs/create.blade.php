@extends('admin.layout')
@section('title', 'Add FAQ | NpAdvanture Admin')
@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">HELP CONTENT</span><h1>Add FAQ</h1></div></div><section class="admin-panel admin-form-panel">@include('admin.faqs.form', ['faq' => null])</section></section>
@endsection
