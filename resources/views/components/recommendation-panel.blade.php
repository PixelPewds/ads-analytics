@props([
    'recommendations' => collect(),
])

@php
    $types = [
        'working'        => ['label' => "What's Working",    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',               'color' => 'emerald'],
        'not_working'    => ['label' => "What's Not Working", 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'red'],
        'at_risk'        => ['label' => 'At Risk',            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'amber'],
        'needs_scaling'  => ['label' => 'Needs Scaling',      'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',                               'color' => 'teal'],
        'recommendations'=> ['label' => 'Recommendations',    'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'color' => 'misty'],
    ];
    $colorMap = [
        'emerald' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => 'text-emerald-500', 'title' => 'text-emerald-700', 'dot' => 'bg-emerald-400'],
        'red'     => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'icon' => 'text-red-400',     'title' => 'text-red-700',     'dot' => 'bg-red-400'],
        'amber'   => ['bg' => 'bg-amber-50',   'border' => 'border-amber-200',   'icon' => 'text-amber-500',   'title' => 'text-amber-700',   'dot' => 'bg-amber-400'],
        'teal'    => ['bg' => 'bg-teal/5',     'border' => 'border-teal/20',     'icon' => 'text-teal',        'title' => 'text-teal',        'dot' => 'bg-teal'],
        'misty'   => ['bg' => 'bg-misty/5',    'border' => 'border-misty/20',    'icon' => 'text-misty',       'title' => 'text-misty',       'dot' => 'bg-misty'],
    ];
@endphp


    {{-- Tab bar --}}
    
        @foreach($types as $key => $type)
            @php
                $count = $recommendations->get($key, collect())->count();
                $c = $colorMap[$type['color']];
            @endphp
            
                {{ $type['label'] }}
                @if($count > 0)
                    
                        {{ $count }}
                    
                @endif
            
        @endforeach
    

    {{-- Tab panels --}}
    @foreach($types as $key => $type)
        @php
            $items = $recommendations->get($key, collect());
            $c     = $colorMap[$type['color']];
        @endphp
        
            @if($items->isEmpty())