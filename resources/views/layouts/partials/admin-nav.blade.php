@php
    $navLinks = [
        ['name' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
        ['name' => 'Categorías', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a22.53 22.53 0 005.246-5.246c.54-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z'],
        ['name' => 'Productos', 'route' => 'admin.products.index', 'active' => 'admin.products.*', 'icon' => 'M12 3.75a.75.75 0 01.75.75v4.31l3-1.732a.75.75 0 011.082.9l-3 5.196L16.29 16.5a.75.75 0 01-1.082 0l-3-5.196-3 5.196a.75.75 0 01-1.082 0l2.458-4.258-3-5.196a.75.75 0 011.082-.9l3 1.732V4.5a.75.75 0 01.75-.75z'],
        ['name' => 'Clientes', 'route' => 'admin.clients.index', 'active' => 'admin.clients.*', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-2.988M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['name' => 'Pedidos', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
        ['name' => 'Reportes', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
        ['name' => 'Configuración', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'icon' => 'M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.795l.75-1.3m7.5-12.99l.75-1.3m-6.063 16.658l.26-1.477m2.605-14.772l.26-1.477m0 17.726l-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.795l-.75-1.3m-7.5-12.99l-.75-1.3m-6.063 16.658l-.26-1.477m2.605-14.772l-.26-1.477m0 17.726l.26-1.477M13.302 4.614l.26-1.477M18.894 17.785l-1.15-.964m-11.49-9.642l-1.15-.964M20.943 14.563l-1.41-.513M4.467 9.95l-1.41-.513M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
@endphp

@foreach($navLinks as $link)
    <a href="{{ route($link['route']) }}" 
       class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold mb-1 transition-all duration-200 
              {{ request()->routeIs($link['active'] ?? $link['route']) 
                 ? 'bg-brand-pink text-white shadow-md' 
                 : 'text-gray-700 hover:text-brand-pink hover:bg-gray-50' }}">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs($link['active'] ?? $link['route']) ? 'text-white' : 'text-gray-400 group-hover:text-brand-pink' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
        </svg>
        {{ $link['name'] }}
    </a>
@endforeach

<div class="mt-8">
    <a href="{{ route('home') }}" target="_blank" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:text-brand-pink hover:bg-gray-50 transition-all duration-200 border-t border-gray-100 pt-4">
        <svg class="h-6 w-6 shrink-0 text-gray-400 group-hover:text-brand-pink" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
        </svg>
        Ver Sitio Web
    </a>
</div>
