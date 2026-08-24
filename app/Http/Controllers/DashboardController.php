<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\Season;
use App\Support\Training;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController
{
    public function index(): View
    {
        $user     = Auth::user();
        $training = $user->isTrainingMember();

        $data = [
            'user'     => $user,
            'training' => $training,
            'season'   => Season::currentLabel(),
        ];

        if ($training) {
            $hours    = Training::cifHours($user);
            $required = Training::hoursRequired();

            $passed = QuizAttempt::query()
                ->where('user_id', $user->getKey())
                ->where('passed', true)
                ->distinct('quiz_id')
                ->count('quiz_id');

            $certCount = $user->certificates()->count();

            $data += [
                'hours'        => $hours,
                'required'     => $required,
                'percent'      => Training::percent($user),
                'history'      => Training::history($user),
                'completions'  => $user->courseCompletions()
                    ->with('course')
                    ->latest('created_at')
                    ->limit(6)
                    ->get(),
                'attempts'     => QuizAttempt::query()
                    ->where('user_id', $user->getKey())
                    ->with('quiz')
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get(),
                'certificates' => $user->certificates()->limit(6)->get(),
                'trainingStats' => [
                    ['label' => 'Season hours', 'value' => rtrim(rtrim(number_format($hours, 2), '0'), '.') . ' / ' . (int) $required, 'sub' => Season::currentLabel() ?? 'No season'],
                    ['label' => 'Assessments passed', 'value' => (string) $passed, 'sub' => 'distinct assessments'],
                    ['label' => 'Certificates', 'value' => (string) $certCount, 'sub' => 'issued to you'],
                ],
            ];
        }

        $exp = $user->years_experience;

        $data['media'] = $user->mediaItems()->limit(12)->get();

        $data['stats'] = [
            ['label' => 'Height',         'value' => $user->height ?: '--'],
            ['label' => 'Weight',         'value' => $user->weight ?: '--'],
            ['label' => 'Location',       'value' => $user->city ?: '--'],
            ['label' => 'Experience',     'value' => $exp !== null ? $exp . ' yr' . ($exp === 1 ? '' : 's') : '--'],
            ['label' => 'Classification', 'value' => $user->classification ?: '--'],
        ];

        return view('member.dashboard', $data);
    }
}
