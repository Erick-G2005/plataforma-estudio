<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <h1 class="text-2xl font-bold">Panel de administrador</h1>

        <div class="grid gap-4 grid-cols-2 sm:grid-cols-4">
            <div class="p-6 bg-white rounded-lg shadow">
                <p class="text-3xl font-bold">{{ $subjectsCount }}</p>
                <p class="text-sm text-gray-500">Materias</p>
            </div>
            <div class="p-6 bg-white rounded-lg shadow">
                <p class="text-3xl font-bold">{{ $topicsCount }}</p>
                <p class="text-sm text-gray-500">Temas</p>
            </div>
            <div class="p-6 bg-white rounded-lg shadow">
                <p class="text-3xl font-bold">{{ $studentsCount }}</p>
                <p class="text-sm text-gray-500">Estudiantes</p>
            </div>
            <div class="p-6 bg-white rounded-lg shadow">
                <p class="text-3xl font-bold">{{ $enrollmentsCount }}</p>
                <p class="text-sm text-gray-500">Inscripciones</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('admin.subjects.index') }}" wire:navigate
               class="p-6 bg-white rounded-lg shadow hover:shadow-md transition">
                <p class="font-semibold">Materias y contenido</p>
                <p class="text-sm text-gray-500 mt-1">Temas, materiales, quizzes y exámenes finales.</p>
            </a>

            <a href="{{ route('admin.students.index') }}" wire:navigate
               class="p-6 bg-white rounded-lg shadow hover:shadow-md transition">
                <p class="font-semibold">Estudiantes</p>
                <p class="text-sm text-gray-500 mt-1">Progreso, inscripciones, intentos y cuentas.</p>
            </a>
        </div>
    </div>
</div>
