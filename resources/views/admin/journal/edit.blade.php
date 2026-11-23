@extends('admin.layout')
@section('title', 'Edit Article | NpAdvanture Admin')
@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">EDITORIAL</span><h1>Edit article</h1></div></div><section class="admin-panel admin-form-panel">@include('admin.journal.form', ['article' => $article])</section></section>
@endsection
