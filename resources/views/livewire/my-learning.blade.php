<div class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div>
            <h1 class="text-2xl font-bold">Mi aprendizaje</h1>
            <p class="text-sm text-gray-500 mt-1">Tus materias y tu avance, a tu propio ritmo.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="p-5 bg-white rounded-lg shadow">
                <p class="text-3xl font-bold">{{ $subjects->count() }}</p>
                <p class="text-sm text-gray-500">Materias inscritas</p>
            </div>
            <div class="p-5 bg-white rounded-lg shadow">
                <p class="text-3xl font-bold">{{ $topicsCompleted }}</p>
                <p class="text-sm text-gray-500">Temas completados</p>
            </div>
            <div class="p-5 bg-white rounded-lg shadow">
                <p class="text-3xl font-bold">{{ $finishedCount }}</p>
                <p class="text-sm text-gray-500">Materias terminadas</p>
            </div>
        </div>

        @if ($subjects->isEmpty())
            <div class="p-10 bg-white rounded-lg shadow text-center space-y-3">
                <p class="text-gray-600">Todavía no estás inscrito en ninguna materia.</p>
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="inline-block text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Descubrir materias
                </a>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($subjects as $subject)
                    <div wire:key="learning-{{ $subject->id }}"
                         class="group relative bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">
                        <x-subject-cover :subject="$subject" class="h-24" letter-size="text-5xl" />

                        <div class="p-5 flex-1 flex flex-col space-y-3">
                            {{-- after:absolute after:inset-0 estira este enlace sobre toda la tarjeta --}}
                            <a href="{{ route('subjects.show', $subject) }}" wire:navigate
                               class="font-semibold text-lg group-hover:text-indigo-600 transition after:absolute after:inset-0">
                                {{ $subject->name }}
                            </a>

                            <div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $subject->progress }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $subject->completed_topics }} de {{ $subject->total_topics }} temas
                                    @if ($subject->has_exam) · examen {{ $subject->exam_passed ? 'aprobado' : 'pendiente' }} @endif
                                    · {{ $subject->progress }}%
                                </p>
                            </div>

                            <div class="flex-1"></div>

                            {{-- relative z-10 deja este botón por encima del enlace extendido --}}
                            @if ($subject->next_step)
                                <a href="{{ $subject->next_url }}" wire:navigate
                                   class="relative z-10 block text-center text-sm font-semibold text-white bg-indigo-600 px-3 py-2 rounded-lg hover:bg-indigo-700">
                                    {{ Str::limit($subject->next_label, 34) }}
                                </a>
                            @else
                                <a href="{{ route('subjects.show', $subject) }}" wire:navigate
                                   class="relative z-10 block text-center text-sm font-semibold text-green-700 bg-green-50 px-3 py-2 rounded-lg hover:bg-green-100">
                                    {{ $subject->completed ? '✓ Completada · Repasar' : 'Ver materia' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
