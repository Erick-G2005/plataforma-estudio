<?php

namespace App\Livewire\Admin;

use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'subjectsCount' => Subject::count(),
            'topicsCount' => Topic::count(),
            'studentsCount' => User::where('role', 'student')->count(),
            'enrollmentsCount' => DB::table('enrollments')->count(),
        ]);
    }
}
