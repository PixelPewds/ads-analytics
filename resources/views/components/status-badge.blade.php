@props(['status' => 'pending'])

@php
$classes = match($status) {
    'processed' => 'badge-teal',
    'failed'    => 'badge-coral',
    default     => 'badge-amber',
};
$dot = match($status) {
    'processed' => 'bg-[#1A7A70]',
    'failed'    => 'bg-[#C0422A]',
    default     => 'bg-[#92400E] animate-pulse',
};
@endphp

<span class="inline-flex items-center gap-1 {{ $classes }} text-xs px-2.5 py-0.5 rounded-full font-semibold">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
    {{ ucfirst($status) }}
</span>
