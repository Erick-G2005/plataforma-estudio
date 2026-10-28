<div class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Panel</a>
            <h1 class="text-2xl font-bold mt-1">Estudiantes</h1>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-2xl font-bold">{{ $totals['all'] }}</p>
                <p class="text-xs text-gray-500">Usuarios en total</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-2xl font-bold">{{ $totals['students'] }}</p>
                <p class="text-xs text-gray-500">Estudiantes</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-2xl font-bold">{{ $totals['admins'] }}</p>
                <p class="text-xs text-gray-500">Administradores</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow">
                <p class="text-2xl font-bold {{ $totals['unverified'] > 0 ? 'text-amber-600' : '' }}">{{ $totals['unverified'] }}</p>
                <p class="text-xs text-gray-500">Correo sin verificar</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <input type="search" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por nombre o correo..."
                   class="flex-1 rounded-lg border-gray-300 text-sm">

            <select wire:model.live="role" class="rounded-lg border-gray-300 text-sm">
                <option value="all">Todos los roles</option>
                <option value="student">Estudiantes</option>
                <option value="admin">Administradores</option>
            </select>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Usuario</th>
                        <th class="px-4 py-3 font-semibold">Rol</th>
                        <th class="px-4 py-3 font-semibold">Materias</th>
                        <th class="px-4 py-3 font-semibold">Correo</th>
                        <th class="px-4 py-3 font-semibold">Registro</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.students.show', $user) }}" wire:navigate
                                   class="font-medium hover:text-indigo-600">{{ $user->name }}</a>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </td>

                            <td class="px-4 py-3">
                                <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $user->isAdmin() ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $user->isAdmin() ? 'Administrador' : 'Estudiante' }}
                                </span>
                            </td>

                            <td class="px-4 py-3">{{ $user->enrolled_subjects_count }}</td>

                            <td class="px-4 py-3">
                                @if ($user->email_verified_at)
                                    <span class="text-xs font-semibold text-green-600">✓ Verificado</span>
                                @else
                                    <span class="text-xs font-semibold text-amber-600">Sin verificar</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-gray-500">
                                {{ $user->created_at?->copy()->timezone('America/Bogota')->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.students.show', $user) }}" wire:navigate
                                   class="text-sm font-medium text-indigo-600 hover:underline">Ver &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
</div>
