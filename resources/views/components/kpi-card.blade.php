@props([
    'label'   => '',
    'value'   => '—',
    'prefix'  => '',
    'suffix'  => '',
    'change'  => null,
    'icon'    => null,
    'color'   => 'teal',
    'sublabel'=> null,
])

@php
    $colorMap = [
        'teal'  => ['icon_bg' => 'bg-[#4297A0]/10', 'icon_text' => 'text-[#4297A0]', 'border' => 'border-l-[#4297A0]'],
        'coral' => ['icon_bg' => 'bg-[#E57F84]/10', 'icon_text' => 'text-[#E57F84]', 'border' => 'border-l-[#E57F84]'],
        'misty' => ['icon_bg' => 'bg-[#2F5061]/10', 'icon_text' => 'text-[#2F5061]', 'border' => 'border-l-[#2F5061]'],
        'amber' => ['icon_bg' => 'bg-amber-100',     'icon_text' => 'text-amber-600',   'border' => 'border-l-amber-400'],
        'emerald'=> ['icon_bg'=> 'bg-emerald-100',   'icon_text' => 'text-emerald-600', 'border' => 'border-l-emerald-400'],
    ];
    $c = $colorMap[$color] ?? $colorMap['teal'];
@endphp

<div class="bg-white rounded-2xl border border-gray-100 border-l-4 {{ $c['border'] }} p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider truncate">{{ $label }}</p>
            <div class="mt-1.5 flex items-baseline gap-1">
                @if($prefix)
                    <span class="text-sm font-medium text-gray-400">{{ $prefix }}</span>
                @endif
                <span class="text-2xl font-bold text-[#2F5061] font-[Barlow] leading-none">{{ $value }}</span>
                @if($suffix)
                    <span class="text-sm font-medium text-gray-400">{{ $suffix }}</span>
                @endif
            </div>
            @if($sublabel)
                <p class="text-xs text-gray-400 mt-1">{{ $sublabel }}</p>
            @endif
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-xl {{ $c['icon_bg'] }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 {{ $c['icon_text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
        @endif
    </div>

    @if($change !== null)
        @php
            $changeVal  = (float)$change;
            $isPositive = $changeVal >= 0;
            $absChange  = abs($changeVal);
        @endphp
        <div class="mt-3 pt-3 border-t border-gray-50 flex items-center gap-1">
            <span class="inline-flex items-center gap-0.5 text-xs font-medium {{ $isPositive ? 'text-emerald-600' : 'text-red-500' }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($isPositive)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    @endif
                </svg>
                {{ $absChange }}%
            </span>
            <span class="text-xs text-gray-400">vs previous</span>
        </div>
    @endif
</div>
