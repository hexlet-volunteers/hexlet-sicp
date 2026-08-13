<?php

namespace App\Http\Controllers;

use App\Components\ActivityChart\ActivityChart;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\ChapterMember;
use App\Models\Comment;
use App\Models\Exercise;
use App\Models\ExerciseMember;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Request;

class HomeController extends Controller
{
    public function index(): View
    {
        if (auth()->guest()) {
            $countComments = Comment::count();
            $countExercises = Exercise::count();
            $countChapters = Chapter::count();
            $countUsers = User::count();

            return view('home.landing', compact(
                'countComments',
                'countExercises',
                'countChapters',
                'countUsers'
            ));
        }

        $filter = Request::query('filter', '');
        $statisticTable = $this->getStatisticTable($filter);
        $logItems = Activity::with('causer', 'subject')->latest()->limit(10)->get()
            ->filter(fn(Activity $activity) => $activity->subject && $activity->causer);
        $activityChart = ActivityChart::all();
        $comments = Comment::with('user', 'commentable')->latest()->limit(10)->get()
            ->filter(fn(Comment $comment) => $comment->user);

        return view('home.index', compact(
            'logItems',
            'activityChart',
            'comments',
            'statisticTable',
        ));
    }

    private function getStatisticTable(string $filter): array
    {
        $periodsForFilter = ['week', 'month'];

        if (!in_array($filter, $periodsForFilter, true)) {
            $countChapterMember = ChapterMember::count();
            $countCompletedExercise = ExerciseMember::count();
            $filter = 'all';
        } else {
            $subPeriod = "sub{$filter}";
            $period = Carbon::today()->$subPeriod(1);

            $countChapterMember = ChapterMember::where('created_at', '>', $period)->count();
            $countCompletedExercise = ExerciseMember::where('created_at', '>', $period)->count();
        }

        return [
            'countChapterMember' => $countChapterMember,
            'countCompletedExercise' => $countCompletedExercise,
            'filterForQuery' => $filter,
        ];
    }
}
