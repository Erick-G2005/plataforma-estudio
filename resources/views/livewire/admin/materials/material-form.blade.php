@php
    $types = [
        'text' => 'Texto con formato',
        'image' => 'Imagen',
        'pdf' => 'PDF',
        'video' => 'Video (YouTube u otro enlace)',
        'link' => 'Enlace externo',
    ];

    $buttonClass = 'px-2.5 py-1 text-xs font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50';
@endphp

<div class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('admin.topics.materials', $topic) }}" wire:navigate
               class="text-sm text-gray-500 hover:underline">&larr; Materiales de {{ $topic->title }}</a>
            <h1 class="text-2xl font-bold mt-1">{{ $materialId ? 'Editar material' : 'Nuevo material' }}</h1>
        </div>

        <div class="grid lg:grid-cols-5 gap-6 items-start">

            {{-- Formulario --}}
            <form wire:submit="save" class="lg:col-span-3 bg-white rounded-xl shadow p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de material</label>
                    <select wire:model.live="type" class="w-full rounded-lg border-gray-300 text-sm">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Título</label>
                    <input type="text" wire:model.blur="title" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Texto con formato --}}
                @if ($type === 'text')
                    <div x-data="{
                        apply(value, start, end) {
                            const el = this.$refs.editor;
                            el.value = value;
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.focus();
                            el.setSelectionRange(start, end);
                        },
                        wrap(before, after, placeholder) {
                            const el = this.$refs.editor;
                            const start = el.selectionStart;
                            const end = el.selectionEnd;
                            const selected = el.value.substring(start, end) || placeholder;
                            const value = el.value.substring(0, start) + before + selected + after + el.value.substring(end);
                            this.apply(value, start + before.length, start + before.length + selected.length);
                        },
                        prefix(marker) {
                            const el = this.$refs.editor;
                            const value = el.value;
                            const start = el.selectionStart;
                            const end = el.selectionEnd;
                            const lineStart = value.lastIndexOf('\n', start - 1) + 1;
                            let lineEnd = value.indexOf('\n', end);
                            if (lineEnd === -1) { lineEnd = value.length; }
                            const block = value.substring(lineStart, lineEnd).split('\n').map((line) => marker + line).join('\n');
                            this.apply(value.substring(0, lineStart) + block + value.substring(lineEnd), lineStart, lineStart + block.length);
                        },
                        link() {
                            const el = this.$refs.editor;
                            const start = el.selectionStart;
                            const end = el.selectionEnd;
                            const selected = el.value.substring(start, end) || 'texto del enlace';
                            const value = el.value.substring(0, start) + '[' + selected + '](https://)' + el.value.substring(end);
                            const urlStart = start + selected.length + 3;
                            this.apply(value, urlStart, urlStart + 8);
                        },
                    }">
                        <label class="block text-sm font-medium mb-1">Contenido</label>

                        <div class="flex flex-wrap gap-1 mb-2">
                            <button type="button" x-on:click="wrap('**', '**', 'texto en negrita')" class="{{ $buttonClass }} font-bold">Negrita</button>
                            <button type="button" x-on:click="wrap('*', '*', 'texto en cursiva')" class="{{ $buttonClass }} italic">Cursiva</button>
                            <button type="button" x-on:click="prefix('## ')" class="{{ $buttonClass }}">Título</button>
                            <button type="button" x-on:click="prefix('### ')" class="{{ $buttonClass }}">Subtítulo</button>
                            <button type="button" x-on:click="prefix('- ')" class="{{ $buttonClass }}">Lista</button>
                            <button type="button" x-on:click="prefix('1. ')" class="{{ $buttonClass }}">Numerada</button>
                            <button type="button" x-on:click="prefix('> ')" class="{{ $buttonClass }}">Cita</button>
                            <button type="button" x-on:click="link()" class="{{ $buttonClass }}">Enlace</button>
                        </div>

                        <textarea x-ref="editor" wire:model.live.debounce.500ms="content" rows="14"
                                  placeholder="Escribe el contenido del material..."
                                  class="w-full rounded-lg border-gray-300 text-sm font-mono"></textarea>

                        @error('content') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                        <p class="mt-2 text-xs text-gray-400">
                            Selecciona un texto y pulsa un botón, o escribe el formato a mano:
                            **negrita**, *cursiva*, ## Título, - lista, 1. numerada, &gt; cita, [texto](https://enlace).
                            Los saltos de línea se respetan.
                        </p>
                    </div>

                {{-- Imagen --}}
                @elseif ($type === 'image')
                    <div>
                        <label class="block text-sm font-medium mb-1">Imagen</label>

                        @if ($existingFile && $existingFile->type === 'image' && ! $file)
                            <div class="mb-2 flex items-center gap-3">
                                <img src="{{ $existingFile->url }}" alt="Imagen actual" class="h-16 rounded">
                                <span class="text-xs text-gray-500">Imagen actual: {{ $existingFile->name }}. Sube otra para reemplazarla.</span>
                            </div>
                        @endif

                        <input type="file" wire:model="file" accept="image/*" class="w-full text-sm">
                        <div wire:loading wire:target="file" class="text-xs text-gray-400">Subiendo...</div>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP o GIF. Máximo 4 MB.</p>
                        @error('file') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción (opcional)</label>
                        <input type="text" wire:model.blur="content" placeholder="Se muestra debajo de la imagen"
                               class="w-full rounded-lg border-gray-300 text-sm">
                        @error('content') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                {{-- PDF --}}
                @elseif ($type === 'pdf')
                    <div class="space-y-3">
                        <label class="block text-sm font-medium">Origen del PDF</label>

                        <div class="flex gap-5 text-sm">
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model.live="pdfSource" value="upload" class="text-indigo-600"> Subir archivo
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model.live="pdfSource" value="url" class="text-indigo-600"> Enlace (URL)
                            </label>
                        </div>

                        @if ($pdfSource === 'upload')
                            @if ($existingFile && $existingFile->type === 'pdf' && ! $file)
                                <p class="text-xs text-gray-500">
                                    Archivo actual:
                                    <a href="{{ $existingFile->url }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ $existingFile->name }}</a>.
                                    Sube otro para reemplazarlo.
                                </p>
                            @endif

                            <input type="file" wire:model="file" accept="application/pdf" class="w-full text-sm">
                            <div wire:loading wire:target="file" class="text-xs text-gray-400">Subiendo...</div>
                            <p class="text-xs text-gray-400">Solo PDF. Máximo 10 MB.</p>
                            @error('file') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        @else
                            <input type="url" wire:model.blur="content" placeholder="https://..."
                                   class="w-full rounded-lg border-gray-300 text-sm">
                            @error('content') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        @endif
                    </div>

                {{-- Video y enlace --}}
                @else
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ $type === 'video' ? 'Enlace del video' : 'Enlace' }}</label>
                        <input type="url" wire:model.blur="content" placeholder="https://..."
                               class="w-full rounded-lg border-gray-300 text-sm">
                        @if ($type === 'video')
                            <p class="text-xs text-gray-400 mt-1">Los enlaces de YouTube se muestran como reproductor dentro de la página.</p>
                        @endif
                        @error('content') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.topics.materials', $topic) }}" wire:navigate class="text-sm text-gray-600 px-4 py-2">Cancelar</a>

                    @unless ($materialId)
                        <button type="button" wire:click="save(true)" wire:loading.attr="disabled" wire:target="file"
                                class="text-sm font-semibold text-indigo-600 px-4 py-2 rounded-lg border border-indigo-200 hover:bg-indigo-50 disabled:opacity-60">
                            Guardar y agregar otro
                        </button>
                    @endunless

                    <button type="submit" wire:loading.attr="disabled" wire:target="file"
                            class="text-sm font-semibold text-white bg-indigo-600 px-5 py-2 rounded-lg hover:bg-indigo-700 disabled:opacity-60">
                        Guardar material
                    </button>
                </div>
            </form>

            {{-- Vista previa --}}
            <div class="lg:col-span-2 lg:sticky lg:top-6">
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5 space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Así lo verá el estudiante</p>
                    <x-material-view :material="$preview" />
                </div>
            </div>
        </div>
    </div>
</div>
