@props([
    'label'  => 'Metric',
    'value'  => '—',
    'icon'   => 'chart-bar',
    'color'  => 'teal',
    'change' => null,   // e.g. '+12.4%' or '-3.1%'
])

@php
$iconPaths = [
    'currency-dollar' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'eye'             => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    'cursor-click'    => 'M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122',
    'trending-up'     => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    'tag'             => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    'chart-bar'       => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    'check-circle'    => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'user'            => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    'chat'            => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    'receipt'         => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
];

$colors = [
    'teal'  => ['bg' => 'bg-[#E6F5F3]', 'icon' => 'text-[#2A9D8F]', 'value' => 'text-[#2A9D8F]'],
    'coral' => ['bg' => 'bg-[#FDF0EC]', 'icon' => 'text-[#E76F51]', 'value' => 'text-[#E76F51]'],
    'slate' => ['bg' => 'bg-[#E8EEF4]', 'icon' => 'text-[#264653]', 'value' => 'text-[#264653]'],
    'blue'  => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600',   'value' => 'text-blue-700'],
];

$c    = $colors[$color] ?? $colors['teal'];
$path = $iconPaths[$icon] ?? $iconPaths['chart-bar'];
@endphp

<div class="kpi-card">
    <div class="flex items-center justify-between mb-3">
        <div class="w-9 h-9 rounded-lg {{ $c['bg'] }} flex items-center justify-center">
            <svg class="w-4.5 h-4.5 {{ $c['icon'] }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
            </svg>
        </div>
        @if($change)
            @php
                $positive = str_starts_with($change, '+');
            @endphp
            <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $positive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                {{ $change }}
            </span>
        @endif
    </div>
    <p class="text-xs text-[#6B7C8D] uppercase tracking-wide font-medium mb-1" style="font-family:'Barlow',sans-serif">
        {{ $label }}
    </p>
    <p class="text-xl font-extrabold {{ $c['value'] }}" style="font-family:'Barlow',sans-serif">
        {{ $value }}
    </p>
</div>