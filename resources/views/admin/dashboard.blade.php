@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Bienvenido al Panel de Administración</h1>
    <p class="text-gray-600">Desde aquí puedes gestionar el catálogo de productos, categorías y configuraciones del sitio.</p>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-brand-blush rounded-lg p-6 border border-brand-pink/20">
            <h3 class="text-lg font-semibold text-brand-pink">Productos</h3>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ \App\Models\Product::count() }}</p>
            <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 hover:text-brand-pink mt-2 inline-block">Ver todos &rarr;</a>
        </div>
        <div class="bg-brand-blush rounded-lg p-6 border border-brand-pink/20">
            <h3 class="text-lg font-semibold text-brand-pink">Categorías</h3>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ \App\Models\Category::count() }}</p>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:text-brand-pink mt-2 inline-block">Ver todas &rarr;</a>
        </div>
    </div>
</div>
@endsection
