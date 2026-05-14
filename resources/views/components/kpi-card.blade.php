@props([
    'label'   => '',
    'value'   => '—',
    'prefix'  => '',
    'suffix'  => '',
    'change'  => null,
    'icon'    => null,
    'color'   => 'teal',
])

@php
    $colorMap = [
        'teal'  => 'bg-teal/10 text-teal',
        'coral' => 'bg-coral/10 text-coral',
        'misty' => 'bg-misty/10 text-misty',
        'amber' => 'bg-amber-100 text-amber-600',
    ];
    $iconBg = $colorMap[$color] ?? $colorMap['teal'];
@endphp


    
        {{ $label }}
        @if($icon)
            
                
                    
                
            
        @endif
    

    
        
            {{ $prefix }}{{ $value }}{{ $suffix }}
        
        @if($change !== null)
            @php
                $isPositive = (float)$change >= 0;
                $absChange = abs((float)$change);
            @endphp
            
                
                    @if($isPositive)
                        
                    @else
                        
                    @endif
                
                {{ $absChange }}%
            
        @endif