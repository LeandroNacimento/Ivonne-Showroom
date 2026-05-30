@extends('layouts.admin')

@section('page_title', 'Productos')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition-colors">Dashboard</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-900">Productos</span>
@endsection

@section('content')
    <livewire:admin.products.product-list />
@endsection
