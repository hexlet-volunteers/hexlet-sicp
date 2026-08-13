<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Solution;
use App\Presenters\ExercisePresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Contracts\View\View;

class SolutionController extends Controller
{
    public function index(Request $request): View
    {
        $filter = array_merge(['name' => null, 'exercise_id' => null], (array)$request->input('filter', []));

        $solutions = QueryBuilder::for(Solution::versioned())
            ->allowedFilters(
                AllowedFilter::exact('exercise_id'),
                AllowedFilter::partial('user.name'),
                AllowedFilter::exact('user_id'),
            )
            ->with(['user', 'exercise'])
            ->whereHas('user')
            ->latest('solutions.created_at')
            ->paginate(50);

        $exercises = Exercise::orderBy('id')->get();
        $exerciseTitles = ExercisePresenter::collection($exercises)
            ->pluck('fullTitle', 'id');

        $solutionAuthors = [];
        if (Auth::user()) {
            $solutionAuthors = [Auth::user()->id => Auth::user()->name];
        }

        return view(
            'solution.index',
            [
                'solutions' => $solutions,
                'filter'    => $filter,
                'exerciseTitles'  => $exerciseTitles,
                'solutionAuthors' => $solutionAuthors,
            ]
        );
    }

    public function show(Solution $solution): View
    {
        if (!$solution->user()->exists()) {
            abort(404);
        }

        $exercise = $solution->exercise;
        $user = $solution->user;
        $solutionsListForCurrentExercise = $solution->exercise
            ->solutions()
            ->where('user_id', $user->id)
            ->get();

        return view('solution.show', [
            'solutionsListForCurrentExercise' => $solutionsListForCurrentExercise,
            'solution' => $solution,
            'currentExercise' => $exercise,
            'user' => $user,
        ]);
    }
}
