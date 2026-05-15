@props([
    'title'    => '',
    'subtitle' => null,
    'class'    => '',
])

<div class="chart-card {{ $class }} {{ $attributes->get('class') }}">
    @if($title)
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-barlow text-sm font-bold text-[#264653]">{{ $title }}</h3>
            @if($subtitle)
            <p class="text-xs text-[#8AABBF] mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
        <div class="flex gap-1.5">{{ $actions }}</div>
        @endisset
    </div>
    @endif

    {{ $slot }}
</div>
