@extends('admin.layouts.app')
@section('title', __('messages.add_new'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.clients') }} — {{ __('messages.add_new') }}</h1></div>
    <a href="{{ route('admin.client.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.client.store') }}" method="POST">
@csrf
@include('admin.client._form')
<div class="d-flex gap-2 mt-4 pb-4">
    <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.Save') }}</button>
    <a href="{{ route('admin.client.index') }}" class="btn-outline-sm">{{ __('messages.Cancel') }}</a>
</div>
</form>

@endsection
