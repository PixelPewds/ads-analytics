@props([
    'headers' => [],
    'empty'   => 'No data available.',
])

<div class="overflow-x-auto">
    <table class="data-table">
        @if(!empty($headers))
        <thead>
            <tr>
                @foreach($headers as $header)
                <th class="{{ $header['class'] ?? '' }}">{{ $header['label'] ?? $header }}</th>
                @endforeach
            </tr>
        </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>

    @if(isset($isEmpty) && $isEmpty)
    <div class="text-center py-8 text-sm text-[#8AABBF]">{{ $empty }}</div>
    @endif
</div>
