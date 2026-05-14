@props([
    'title'    => '',
    'subtitle' => null,
    'height'   => '280',
    'id'       => 'chart',
])


    
        
            {{ $title }}
            @if($subtitle)
                {{ $subtitle }}
            @endif
        
        @if(isset($actions))
            {{ $actions }}
        @endif
    