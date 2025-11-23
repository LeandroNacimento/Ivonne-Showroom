@extends('layouts.app')

@section('content')
<div class="bg-white">
    <!-- Hero About -->
    <div class="relative py-16 bg-brand-blush">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold text-gray-900 font-script text-brand-pink">Sobre Ivonne Showroom</h1>
            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">Más que moda, un estilo de vida.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-6">Nuestra Historia</h2>
                <p class="text-lg text-gray-600 mb-6">
                    Ivonne Showroom nació en Formosa con el sueño de ofrecer a la mujer moderna prendas únicas que resalten su personalidad. 
                    Comenzamos como un pequeño emprendimiento y gracias a la confianza de nuestras clientas, hoy somos un referente de moda en la ciudad.
                </p>
                <p class="text-lg text-gray-600 mb-6">
                    Nos especializamos en seleccionar cada prenda pensando en la calidad, la comodidad y las últimas tendencias, sin perder ese toque clásico y elegante que nos caracteriza.
                </p>
            </div>
            <div class="mt-10 lg:mt-0">
                <div class="aspect-w-3 aspect-h-2 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400">Foto del Showroom / Ivonne</span>
                </div>
            </div>
        </div>

        <div class="mt-20">
            <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-12">Nuestros Valores</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-brand-pink text-white mx-auto mb-4">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Pasión</h3>
                    <p class="mt-2 text-base text-gray-500">Amamos lo que hacemos y eso se refleja en cada detalle de nuestra atención.</p>
                </div>

                <div class="text-center">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-brand-pink text-white mx-auto mb-4">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Calidad</h3>
                    <p class="mt-2 text-base text-gray-500">Seleccionamos telas y confecciones de primera para garantizar durabilidad.</p>
                </div>

                <div class="text-center">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-brand-pink text-white mx-auto mb-4">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Cercanía</h3>
                    <p class="mt-2 text-base text-gray-500">Queremos que te sientas cómoda y segura con tu elección.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
