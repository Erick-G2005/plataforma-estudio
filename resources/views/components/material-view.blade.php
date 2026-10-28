@props(['material'])

@php
    $type = data_get($material, 'type');
    $title = data_get($material, 'title');
    $content = (string) data_get($material, 'content', '');
    $fileUrl = data_get($material, 'file_url');
    $fileName = data_get($material, 'file_name');
    $pending = (bool) data_get($material, 'pending_upload', false);

    // Solo se usan enlaces http o https
    $safeUrl = preg_match('#^https?://#i', $content) ? $content : null;

    $youtubeId = null;

    if ($type === 'video' && $safeUrl) {
        preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]+)/', $safeUrl, $matches);
        $youtubeId = $matches[1] ?? null;
    }
@endphp

<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="font-semibold mb-3">{{ $title }}</h2>

    @if ($type === 'text')
        <x-markdown :content="$content" />

    @elseif ($type === 'image')
        @if ($fileUrl)
            <figure>
                <img src="{{ $fileUrl }}" alt="{{ $content !== '' ? $content : $title }}"
                     class="mx-auto max-w-full rounded-lg">
                @if ($content !== '')
                    <figcaption class="mt-2 text-center text-sm text-gray-500">{{ $content }}</figcaption>
                @endif
            </figure>
        @elseif ($pending)
            <p class="text-sm text-gray-500">Imagen seleccionada: {{ $fileName }}. Se verá al guardar.</p>
        @else
            <p class="text-sm text-gray-400">Todavía no hay una imagen.</p>
        @endif

    @elseif ($type === 'video')
        @if ($youtubeId)
            <div class="aspect-video">
                <iframe class="h-full w-full rounded-lg"
                        src="https://www.youtube.com/embed/{{ $youtubeId }}"
                        title="{{ $title }}"
                        frameborder="0"
                        allowfullscreen></iframe>
            </div>
        @elseif ($safeUrl)
            <a href="{{ $safeUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:underline">
                ▶ Ver video
            </a>
        @else
            <p class="text-sm text-gray-400">Pega el enlace del video para verlo aquí.</p>
        @endif

    @elseif ($type === 'pdf')
        @if ($fileUrl)
            <iframe src="{{ $fileUrl }}" title="{{ $title }}"
                    class="h-[70vh] w-full rounded-lg border border-gray-200"></iframe>

            <div class="mt-3 flex flex-wrap gap-4 text-sm font-semibold">
                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">Abrir en una pestaña nueva</a>
                <a href="{{ $fileUrl }}" download="{{ $fileName }}" class="text-indigo-600 hover:underline">Descargar</a>
            </div>
        @elseif ($pending)
            <p class="text-sm text-gray-500">PDF seleccionado: {{ $fileName }}. Se verá al guardar.</p>
        @elseif ($safeUrl)
            <a href="{{ $safeUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:underline">
                📄 Abrir PDF
            </a>
        @else
            <p class="text-sm text-gray-400">Sube un PDF o pega su enlace para verlo aquí.</p>
        @endif

    @elseif ($type === 'link')
        @if ($safeUrl)
            <a href="{{ $safeUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 break-all text-sm font-semibold text-indigo-600 hover:underline">
                🔗 {{ $safeUrl }}
            </a>
        @else
            <p class="text-sm text-gray-400">Pega un enlace para verlo aquí.</p>
        @endif
    @endif
</div>
