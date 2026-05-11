@props([
    'label'     => '',
    'value'     => '—',
    'trend'     => null,   // '+12.3%' or '-4.2%'
    'trendDir'  => null,   // 'up' | 'down' | 'flat'
    'sub'       => null,
    'icon'      => null,
    'color'     => 'teal', // 'teal' | 'coral' | 'blue'
])

@php
    $iconColors = [
        'teal'  => 'bg-teal-green/10 text-teal-green',
        'coral' => 'bg-coral/10 text-coral',
        'blue'  => 'bg-misty-blue/10 text-misty-blue',
    ];
    $iconColor = $iconColors[$color] ?? $iconColors['teal'];
@endphp

merge(['class' => 'card-padded reveal']) }}>
    
        {{ $label }}
        @if($icon)
            
                {!! $icon !!}
            
        @endif
    

    
        {{ $value }}
    

    @if($trend || $sub)
        
            @if($trend)
                
                    @if($trendDir === 'up')
                        
                            
                        
                    @elseif($trendDir === 'down')
                        
                            
                        
                    @endif
                    {{ $trend }}
                
            @endif
            @if($sub)
                {{ $sub }}
            @endif
        
    @endif
