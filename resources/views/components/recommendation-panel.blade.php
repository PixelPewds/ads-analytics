@props(['recommendations', 'report'])

@php
$typeConfig = [
    'working'         => ['label' => "What's Working",     'badge' => 'badge-working',     'icon' => '✅', 'border' => 'border-green-200'],
    'not_working'     => ['label' => "What's Not Working", 'badge' => 'badge-not-working',  'icon' => '❌', 'border' => 'border-red-200'],
    'at_risk'         => ['label' => 'At Risk',            'badge' => 'badge-at-risk',      'icon' => '⚠️', 'border' => 'border-yellow-200'],
    'needs_scaling'   => ['label' => 'Needs Scaling',      'badge' => 'badge-scaling',      'icon' => '🚀', 'border' => 'border-blue-200'],
    'recommendations' => ['label' => 'Recommendations',   'badge' => 'badge-recs',         'icon' => '💡', 'border' => 'border-violet-200'],
];
@endphp

<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-[#264653]" style="font-family:'Barlow',sans-serif">
            AI Insights &amp; Recommendations
        </h3>
        <form method="POST" action="{{ route('reports.regenerate', $report) }}">
            @csrf
            <button class="btn-primary text-xs py-1.5 px-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Regenerate
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($typeConfig as $type => $cfg)
            @if($recommendations->has($type))
            <div class="bg-white border {{ $cfg['border'] }} rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg leading-none">{{ $cfg['icon'] }}</span>
                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $cfg['badge'] }}">
                        {{ $cfg['label'] }}
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($recommendations[$type] as $rec)
                    <div>
                        <p class="text-sm font-semibold text-[#264653] mb-0.5">{{ $rec->title }}</p>
                        <p class="text-xs text-[#6B7C8D] leading-relaxed">{{ $rec->content }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>