<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Página de inicio para visitantes, con datos reales de la plataforma.
     */
    public function __invoke(): View
    {
        return view('landing', [
            'subjects' => Subject::withCount('topics')->orderBy('order')->take(6)->get(),
            'stats' => [
                'subjects' => Subject::count(),
                'topics' => Topic::count(),
                'materials' => Material::count(),
                'quizzes' => Quiz::count(),
            ],
        ]);
    }
}
