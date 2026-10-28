<?php

namespace App\Livewire\Admin\Students;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $role = 'all'; // all | student | admin

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->withCount('enrolledSubjects')
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';

                $query->where(function ($w) use ($term) {
                    $w->where('name', 'like', $term)->orWhere('email', 'like', $term);
                });
            })
            ->when($this->role !== 'all', fn ($query) => $query->where('role', $this->role))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.students.index', [
            'users' => $users,
            'totals' => [
                'all' => User::count(),
                'students' => User::where('role', 'student')->count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
        ]);
    }
}
