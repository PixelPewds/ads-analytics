@props([
    'recommendations' => collect(),
])

@php
    $types = [
        'working'         => ['label' => "What's Working",    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',               'color' => 'emerald'],
        'not_working'     => ['label' => "What's Not Working", 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'red'],
        'at_risk'         => ['label' => 'At Risk',            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'amber'],
        'needs_scaling'   => ['label' => 'Needs Scaling',      'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',                               'color' => 'teal'],
        'recommendations' => ['label' => 'Recommendations',   'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'color' => 'misty'],
    ];

    $colorMap = [
        'emerald' => ['bg' => 'bg-emerald-50',  'border' => 'border-emerald-200', 'icon' => 'text-emerald-500', 'title' => 'text-emerald-700', 'dot' => 'bg-emerald-400', 'tab_active' => 'border-emerald-500 text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700'],
        'red'     => ['bg' => 'bg-red-50',       'border' => 'border-red-200',     'icon' => 'text-red-400',     'title' => 'text-red-700',     'dot' => 'bg-red-400',     'tab_active' => 'border-red-500 text-red-600',         'badge' => 'bg-red-100 text-red-700'],
        'amber'   => ['bg' => 'bg-amber-50',     'border' => 'border-amber-200',   'icon' => 'text-amber-500',   'title' => 'text-amber-700',   'dot' => 'bg-amber-400',   'tab_active' => 'border-amber-500 text-amber-600',     'badge' => 'bg-amber-100 text-amber-700'],
        'teal'    => ['bg' => 'bg-[#4297A0]/5',  'border' => 'border-[#4297A0]/20','icon' => 'text-[#4297A0]',  'title' => 'text-[#4297A0]',   'dot' => 'bg-[#4297A0]',   'tab_active' => 'border-[#4297A0] text-[#4297A0]',    'badge' => 'bg-[#4297A0]/10 text-[#4297A0]'],
        'misty'   => ['bg' => 'bg-[#2F5061]/5',  'border' => 'border-[#2F5061]/20','icon' => 'text-[#2F5061]',  'title' => 'text-[#2F5061]',   'dot' => 'bg-[#2F5061]',   'tab_active' => 'border-[#2F5061] text-[#2F5061]',    'badge' => 'bg-[#2F5061]/10 text-[#2F5061]'],
    ];

    $firstType = array_key_first($types);
    foreach ($types as $key => $type) {
        $count = $recommendations->get($key, collect())->count();
        if ($count > 0) { $firstType = $key; break; }
    }
@endphp

<div x-data="{ activeTab: '{{ $firstType }}' }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Tab bar --}}
    <div class="flex overflow-x-auto scrollbar-thin border-b border-gray-100">
        @foreach($types as $key => $type)
            @php
                $count = $recommendations->get($key, collect())->count();
                $c = $colorMap[$type['color']];
            @endphp
            <button
                @click="activeTab = '{{ $key }}'"
                class="flex items-center gap-2 px-4 py-3.5 text-xs font-semibold whitespace-nowrap border-b-2 transition-all"
                :class="activeTab === '{{ $key }}' ? 'border-b-2 {{ $c['tab_active'] }} bg-gray-50/70' : 'border-transparent text-gray-500 hover:text-gray-700'"
            >
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $type['icon'] }}"/>
                </svg>
                <span class="hidden sm:inline">{{ $type['label'] }}</span>
                <span class="sm:hidden">{{ Str::limit($type['label'], 8, '') }}</span>
                @if($count > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs {{ $c['badge'] }}">{{ $count }}</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Tab panels --}}
    @foreach($types as $key => $type)
        @php
            $items = $recommendations->get($key, collect());
            $c     = $colorMap[$type['color']];
        @endphp
        <div x-show="activeTab === '{{ $key }}'" x-cloak class="p-5">
            @if($items->isEmpty())
                <div class="text-center py-10">
                    <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $type['icon'] }}"/>
                    </svg>
                    <p class="text-sm text-gray-400">No insights in this category yet.</p>
                    <p class="text-xs text-gray-300 mt-1">Upload a report and connect OpenAI to generate recommendations.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($items as $item)
                        <div class="rounded-xl border {{ $c['border'] }} {{ $c['bg'] }} p-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 w-2 h-2 rounded-full {{ $c['dot'] }} shrink-0 mt-1.5"></div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold {{ $c['title'] }} font-[Barlow]">{{ $item->title }}</h4>
                                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $item->content }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
