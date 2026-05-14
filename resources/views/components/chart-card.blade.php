@props([
    'title'    => '',
    'subtitle' => null,
    'height'   => '280',
    'id'       => 'chart',
])

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-start justify-between mb-4 gap-3">
        <div>
            <h3 class="text-sm font-semibold text-[#2F5061] font-[Barlow]">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-xs text-gray-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="shrink-0">{{ $actions }}</div>
        @endif
    </div>
    <div style="height: {{ $height }}px; position: relative;">
        <canvas id="{{ $id }}"></canvas>
    </div>
</div>
