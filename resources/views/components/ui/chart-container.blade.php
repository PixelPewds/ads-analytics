@props(['variant' => 'gray', 'label' => ''])

@php
    $classes = match($variant) {
        'green'  => 'badge-green',
        'coral'  => 'badge-coral',
        'blue'   => 'badge-blue',
        'yellow' => 'badge-yellow',
        default  => 'badge-gray',
    };
@endphp

merge(['class' => $classes]) }}>
    {{ $label ?: $slot }}
