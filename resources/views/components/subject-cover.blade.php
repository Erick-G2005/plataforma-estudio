@props(['subject', 'letterSize' => 'text-7xl'])

@php
    $palettes = [
        'from-indigo-500 to-purple-600',
        'from-emerald-500 to-teal-600',
        'from-rose-500 to-orange-500',
        'from-sky-500 to-blue-600',
        'from-amber-500 to-red-500',
        'from-fuchsia-500 to-pink-600',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-gradient-to-br flex items-center justify-center ' . $palettes[$subject->id % count($palettes)]]) }}>
    @if ($subject->cover_url)
        <img src="{{ $subject->cover_url }}" alt="{{ $subject->name }}"
             class="absolute inset-0 w-full h-full object-cover transition duration-300 group-hover:scale-105">
    @else
        <span class="font-black text-white/30 {{ $letterSize }}">{{ mb_strtoupper(mb_substr($subject->name, 0, 1)) }}</span>
    @endif

    {{ $slot }}
</div>