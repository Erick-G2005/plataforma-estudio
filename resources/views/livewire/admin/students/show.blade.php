@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    $tones = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'info' => 'bg-indigo-50 border-indigo-200 text-indigo-800',
        'error' => 'bg-red-50 border-red-200 text-red-700',
    ];
@endphp

<div class="py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.students.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Estudiantes</a>
                <h1 class="text-2xl font-bold mt-1">{{ $student->name }}</h1>
                <p class="text-sm text-gray-500">{{ $student->email }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    Registrado el {{ $student->created_at?->copy()->timezone('America/Bogota')->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $student->isAdmin() ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $student->isAdmin() ? 'Administrador' : 'Estudiante' }}
                </span>
            </div>
        </div>

        @if ($notice !== '')
            <div class="p-4 rounded-lg border text-sm {{ $tones[$noticeType] ?? $tones['success'] }}">{{ $notice }}</div>
        @endif

        {{-- Cuenta --}}
        <div class="p-6 bg-white rounded-lg shadow space-y-5">
            <h2 class="font-semibold">Cuenta</h2>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <p class="text-sm font-medium">Rol</p>
                    <p class="text-sm text-gray-600">{{ $student->isAdmin() ? 'Administrador' : 'Estudiante' }}</p>

                    @if ($isSelf)
                        <p class="text-xs text-gray-400">Esta es tu cuenta: no puedes cambiar tu propio rol.</p>
                    @elseif ($student->isAdmin())
                        <button type="button" wire:click="setRole('student')"
                                wire:confirm="¿Quitar el rol de administrador a {{ $student->name }}?"
                                class="text-sm font-medium text-red-600 hover:underline">Quitar rol de administrador</button>
                    @else
                        <button type="button" wire:click="setRole('admin')"
                                wire:confirm="¿Dar rol de administrador a {{ $student->name }}? Podrá gestionar todo el contenido y los estudiantes."
                                class="text-sm font-medium text-indigo-600 hover:underline">Hacer administrador</button>
                    @endif
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-medium">Contraseña temporal</p>

                    <form wire:submit="setPassword" class="space-y-2">
                        <div class="flex gap-2">
                            <input type="text" wire:model="newPassword" autocomplete="off" placeholder="Mínimo 8 caracteres"
                                   class="min-w-0 flex-1 rounded-lg border-gray-300 text-sm">
                            <button type="button" wire:click="generatePassword"
                                    class="shrink-0 text-xs font-medium text-gray-600 border border-gray-300 px-2 rounded-lg hover:bg-gray-50">Generar</button>
                        </div>
                        @error('newPassword') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                        <button type="submit"
                                class="text-sm font-medium text-indigo-600 hover:underline">Establecer contraseña</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Materias --}}
        <div class="p-6 bg-white rounded-lg shadow space-y-4">
            <h2 class="font-semibold">Materias <span class="font-normal text-gray-400">({{ $overview->count() }})</span></h2>

            @forelse ($overview as $subject)
                <div wire:key="enr-{{ $subject->id }}" class="p-4 border rounded-lg space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('subjects.show', $subject) }}" target="_blank" rel="noopener"
                               class="font-medium hover:text-indigo-600">{{ $subject->name }}</a>
                            <p class="text-xs text-gray-500">
                                {{ $subject->completed_topics }} de {{ $subject->total_topics }} temas
                                @if ($subject->has_exam) · examen {{ $subject->exam_passed ? 'aprobado' : 'pendiente' }} @endif
                                · {{ $subject->progress }}%
                            </p>
                        </div>

                        <span class="shrink-0 text-xs font-semibold px-2 py-1 rounded-full {{ $subject->completed ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $subject->completed ? 'Completada' : 'En curso' }}
                        </span>
                    </div>

                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $subject->progress }}%"></div>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                        <span class="text-gray-500">
                            Siguiente: {{ $subject->next_step ? Str::limit($subject->next_label, 40) : '—' }}
                        </span>

                        <button type="button" wire:click="unenroll({{ $subject->id }})"
                                wire:confirm="¿Desinscribir a {{ $student->name }} de «{{ $subject->name }}»? Su progreso se conserva."
                                class="font-medium text-gray-600 hover:underline">Desinscribir</button>

                        <button type="button" wire:click="resetSubjectProgress({{ $subject->id }})"
                                wire:confirm="¿Reiniciar el progreso de «{{ $subject->name }}»? Se borran los temas estudiados y todos sus intentos de quizzes y examen. No se puede deshacer."
                                class="font-medium text-red-600 hover:underline">Reiniciar progreso</button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No está inscrito en ninguna materia.</p>
            @endforelse

            @if ($available->isNotEmpty())
                <form wire:submit="enroll" class="flex flex-col sm:flex-row gap-2 pt-2 border-t">
                    <select wire:model="enrollSubjectId" class="flex-1 rounded-lg border-gray-300 text-sm">
                        <option value="">Inscribir en una materia...</option>
                        @foreach ($available as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700">
                        Inscribir
                    </button>
                </form>
            @endif
        </div>

        {{-- Quizzes y exámenes --}}
        <div class="p-6 bg-white rounded-lg shadow space-y-4">
            <h2 class="font-semibold">Quizzes y exámenes</h2>

            @if ($quizRows->isEmpty())
                <p class="text-sm text-gray-500">Todavía no ha presentado ningún quiz.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-2 pe-4 font-semibold">Quiz</th>
                                <th class="py-2 pe-4 font-semibold">Intentos</th>
                                <th class="py-2 pe-4 font-semibold">Mejor nota</th>
                                <th class="py-2 pe-4 font-semibold">Estado</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach ($quizRows as $row)
                                <tr wire:key="quiz-{{ $row->quiz->id }}">
                                    <td class="py-3 pe-4">
                                        <p class="font-medium">{{ $row->quiz->title }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $row->quiz->isFinal() ? 'Examen final' : 'Quiz del tema' }}
                                            @if ($row->subject) · {{ $row->subject->name }} @endif
                                        </p>
                                    </td>

                                    <td class="py-3 pe-4">{{ $row->status->attempts_total }}</td>

                                    <td class="py-3 pe-4">
                                        {{ $row->status->best_percentage !== null ? $fmt($row->status->best_percentage) . '%' : '—' }}
                                    </td>

                                    <td class="py-3 pe-4">
                                        @if ($row->status->passed)
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">Aprobado</span>
                                        @elseif ($row->status->in_progress)
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-indigo-100 text-indigo-700">En curso</span>
                                        @elseif ($row->status->locked_until)
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700">
                                                Bloqueado · {{ $row->status->locked_until->copy()->locale('es')->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-700">No aprobado</span>
                                        @endif
                                    </td>

                                    <td class="py-3 text-right whitespace-nowrap">
                                        @if ($row->last)
                                            <a href="{{ route('quizzes.attempt', ['quiz' => $row->quiz->id, 'attempt' => $row->last->id]) }}"
                                               target="_blank" rel="noopener"
                                               class="text-xs font-medium text-indigo-600 hover:underline">Ver último intento</a>
                                        @endif

                                        @unless ($row->status->passed)
                                            <button type="button" wire:click="resetAttempts({{ $row->quiz->id }})"
                                                    wire:confirm="¿Reiniciar los intentos de {{ $student->name }} en «{{ $row->quiz->title }}»? Se borran sus intentos no aprobados y se quita cualquier bloqueo."
                                                    class="ms-3 text-xs font-medium text-red-600 hover:underline">Reiniciar intentos</button>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Zona de peligro --}}
        <div class="pt-2">
            @if (! $isSelf && ! $student->isAdmin())
                <button type="button" wire:click="deleteStudent"
                        wire:confirm="¿Eliminar la cuenta de {{ $student->name }}? Se borran sus inscripciones, su progreso y todos sus intentos. No se puede deshacer."
                        class="text-xs font-medium text-red-600 hover:underline">Eliminar cuenta de este estudiante</button>
            @else
                <p class="text-xs text-gray-400">
                    {{ $isSelf ? 'Tu propia cuenta no se puede eliminar desde aquí.' : 'Para eliminar a un administrador, primero quítale ese rol.' }}
                </p>
            @endif
        </div>
    </div>
</div>
