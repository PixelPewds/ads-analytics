@props([
    'label'       => '',
    'value'       => '',
    'icon'        => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    'color'       => '#2A9D8F',
    'bg'          => '#E6F7F5',
    'change'      => null,
    'changePct'   => null,
    'subLabel'    => null,
])

@php
$positive = $changePct !== null && $changePct >= 0;
@endphp

<div class="kpi-card {{ $attributes->get('class') }}">
    <div class="flex items-center gap-2 mb-2.5">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background: {{ $bg }}">
            <svg class="w-4 h-4" fill="none" stroke="{{ $color }}" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/>
            </svg>
        </div>
        <span class="text-xs font-semibold text-[#6B7C8D] uppercase tracking-wide leading-tight">
            {{ $label }}
        </span>
    </div>

    <div class="text-xl font-bold font-barlow text-[#0F1C28]">{{ $value }}</div>

    @if($subLabel)
    <div class="text-xs text-[#8AABBF] mt-1">{{ $subLabel }}</div>
    @endif

    @if($changePct !== null)
    <div class="flex items-center gap-1 mt-2">
        <span class="text-xs font-semibold {{ $positive ? 'text-[#1A7A70]' : 'text-[#C0422A]' }}">
            {{ $positive ? '▲' : '▼' }} {{ abs($changePct) }}%
        </span>
        <span class="text-[10px] text-[#8AABBF]">vs prev. period</span>
    </div>
    @endif

    {{ $slot }}
</div>
