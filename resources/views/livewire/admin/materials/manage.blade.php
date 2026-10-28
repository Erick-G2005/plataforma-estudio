@php
    $labels = ['text' => 'Texto', 'image' => 'Imagen', 'pdf' => 'PDF', 'video' => 'Video', 'link' => 'Enlace'];
    $icons = ['text' => '📝', 'image' => '🖼️', 'pdf' => '📄', 'video' => '▶️', 'link' => '🔗'];
@endphp

<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.subjects.topics', $topic->subject) }}" wire:navigate
                   class="text-sm text-gray-500 hover:underline">&larr; Temas de {{ $topic->subject->name }}</a>
                <h1 class="text-2xl font-bold mt-1">Materiales de {{ $topic->title }}</h1>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('topics.show', $topic) }}" target="_blank" rel="noopener"
                   class="text-sm font-semibold text-indigo-600 border border-indigo-200 px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Ver como estudiante ↗
                </a>

                <a href="{{ route('admin.topics.materials.create', $topic) }}" wire:navigate
                   class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                    + Nuevo material
                </a>
            </div>
        </div>

        @if ($materials->isEmpty())
            <div class="p-6 bg-white rounded-lg shadow text-sm text-gray-500">
                Este tema todavía no tiene materiales. Crea el primero con «+ Nuevo material».
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <ul class="divide-y"
                    x-data="{
                        sortable: null,
                        init() {
                            const boot = () => {
                                if (! window.Sortable) { return setTimeout(boot, 100); }
                                this.sortable = window.Sortable.create(this.$el, {
                                    handle: '[data-handle]',
                                    animation: 150,
                                    onEnd: () => {
                                        const ids = Array.from(this.$el.querySelectorAll('[data-id]')).map((el) => parseInt(el.dataset.id));
                                        $wire.reorder(ids);
                                    },
                                });
                            };
                            boot();
                        },
                        destroy() { if (this.sortable) { this.sortable.destroy(); } },
                    }">
                    @foreach ($materials as $material)
                        <li wire:key="material-{{ $material->id }}" data-id="{{ $material->id }}"
                            class="flex items-center gap-3 p-4 bg-white">
                            <span data-handle title="Arrastra para reordenar"
                                  class="cursor-grab select-none text-lg leading-none text-gray-300 hover:text-gray-500">⋮⋮</span>

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-base">
                                {{ $icons[$material->type] ?? '📄' }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium truncate">{{ $material->title }}</p>
                                <p class="text-xs text-gray-500 truncate">
                                    <span class="font-semibold">{{ $labels[$material->type] ?? $material->type }}</span>
                                    ·
                                    @if ($material->type === 'text')
                                        {{ Str::limit(trim(strip_tags((string) $material->content)), 80) }}
                                    @elseif ($material->file_path)
                                        {{ $material->file_name }}
                                    @else
                                        {{ $material->content }}
                                    @endif
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-3 text-xs">
                                <button type="button" wire:click="move({{ $material->id }}, 'up')" @disabled($loop->first)
                                        title="Subir" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↑</button>
                                <button type="button" wire:click="move({{ $material->id }}, 'down')" @disabled($loop->last)
                                        title="Bajar" class="text-gray-400 hover:text-gray-700 disabled:opacity-30">↓</button>

                                <a href="{{ route('admin.materials.edit', $material) }}" wire:navigate
                                   class="font-medium text-indigo-600 hover:underline">Editar</a>

                                <button type="button" wire:click="duplicate({{ $material->id }})"
                                        class="font-medium text-gray-600 hover:underline">Duplicar</button>

                                <button type="button" wire:click="delete({{ $material->id }})"
                                        wire:confirm="¿Seguro que quieres eliminar este material?{{ $material->file_path ? ' También se borrará el archivo subido.' : '' }}"
                                        class="font-medium text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-xs text-gray-400">Arrastra el icono ⋮⋮ para cambiar el orden, o usa las flechas. El estudiante ve los materiales en este mismo orden.</p>
        @endif
    </div>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endassets
