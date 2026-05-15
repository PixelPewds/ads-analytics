{{-- Usage: @include('partials.nav-link', ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => '...']) --}}

@props(['route' => '#', 'label' => '', 'icon' => '', 'matchRoutes' => []])

@php
$active = request()->routeIs($route) || collect($matchRoutes)->contains(fn($r) => request()->routeIs($r));
@endphp

<a href="{{ route($route) }}"
   class="nav-link {{ $active ? 'active' : '' }}">
    @if($icon)
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/>
    </svg>
    @endif
    {{ $label }}
</a>
