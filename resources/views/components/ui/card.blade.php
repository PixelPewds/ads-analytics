@props(['padded' => true, 'title' => null, 'subtitle' => null])

merge(['class' => 'card reveal']) }}>
    @if($title)
        
            
                {{ $title }}
                @if($subtitle)
                    {{ $subtitle }}
                @endif
            
            @isset($actions)
                {{ $actions }}
            @endisset
        
    @endif

    
        {{ $slot }}
    