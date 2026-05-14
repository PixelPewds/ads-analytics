@props(['title' => '', 'subtitle' => ''])

<div class="chart-card">
    @if($title)
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-[#264653]" style="font-family:'Barlow',sans-serif">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-xs text-[#6B7C8D] mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        {{ $actions ?? '' }}
    </div>
    @endif
    {{ $slot }}
</div>