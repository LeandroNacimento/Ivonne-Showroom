@extends('layouts.admin')

@section('page_title', 'Clientes')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition-colors">Dashboard</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-900">Clientes</span>
@endsection

@section('content')
    <livewire:admin.clients.client-list />
@endsection
