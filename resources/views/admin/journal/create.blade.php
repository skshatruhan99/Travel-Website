@extends('admin.layout')
@section('title', 'New Article | NpAdvanture Admin')
@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">EDITORIAL</span><h1>New article</h1></div></div><section class="admin-panel admin-form-panel">@include('admin.journal.form', ['article' => null])</section></section>
@endsection
