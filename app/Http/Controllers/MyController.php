<?php

namespace App\Http\Controllers;

use App\DTO\Progress\ChapterNodeData;
use App\DTO\Progress\MyProgressPageData;
use App\Models\User;
use App\Services\ChapterProgressService;
use Auth;
use Inertia\Response;

class MyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(ChapterProgressService $chapterProgressService): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('exerciseMembers', 'chapterMembers');

        $tree = $chapterProgressService->buildTreeProgress(
            $user->chapterMembers->keyBy('chapter_id'),
            $user->exerciseMembers->keyBy('exercise_id'),
        );

        $page = new MyProgressPageData(
            userName: $user->name,
            userUrl: route('users.show', $user),
            solutionsUrl: route('my.solutions.index'),
            chapters: $tree->map(ChapterNodeData::fromProgress(...))->values()->all(),
        );

        return $this->inertia($page->toArray())
            ->withViewData([
                'robots' => 'noindex, nofollow',
                'description' => __('my.description'),
            ]);
    }
}
