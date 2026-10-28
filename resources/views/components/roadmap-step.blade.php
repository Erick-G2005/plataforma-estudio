@props([
    'href' => null,
    'status' => 'todo',   // done | active | todo | locked
    'marker' => null,     // contenido del círculo: número, ✓, 🔒...
    'title',
    'subtitle' => null,
    'badge' => null,
    'tone' => 'gray',     // gray | green | indigo | amber | purple
    'variant' => 'topic', // topic | quiz | exam
])

@php
    $tag = $href ? 'a' : 'div';

    $markerClasses = [
        'done' => 'bg-green-100 text-green-700',
        'active' => 'bg-indigo-600 text-white',
        'todo' => 'bg-gray-100 text-gray-600',
        'locked' => 'bg-gray-100 text-gray-400',
    ][$status] ?? 'bg-gray-100 text-gray-600';

    $badgeClasses = [
        'gray' => 'bg-gray-100 text-gray-600',
        'green' => 'bg-green-100 text-green-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'purple' => 'bg-purple-100 text-purple-700',
    ][$tone] ?? 'bg-gray-100 text-gray-600';

    $box = match ($variant) {
        'quiz' => 'ms-8 border border-dashed border-gray-300 bg-white/60',
        'exam' => 'border border-purple-200 bg-purple-50',
        default => 'bg-white shadow',
    };
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" wire:navigate @endif
    class="flex items-center justify-between gap-4 rounded-xl p-4 transition {{ $box }} {{ $href ? 'cursor-pointer hover:shadow-md' : 'opacity-70' }}">
    <div class="flex items-center gap-3 min-w-0">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $markerClasses }}">{{ $marker }}</span>

        <div class="min-w-0">
            <p class="font-medium truncate">{{ $title }}</p>
            @if ($subtitle)
                <p class="text-xs text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if ($badge)
        <span class="shrink-0 text-xs font-semibold px-2 py-1 rounded-full {{ $badgeClasses }}">{{ $badge }}</span>
    @endif
</{{ $tag }}>
