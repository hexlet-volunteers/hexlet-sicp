<?php

namespace App\Http\Controllers\My;

use App\DTO\My\SolutionListItemData;
use App\DTO\My\SolutionListPageData;
use App\DTO\PaginationData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Inertia\Response;

class SolutionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $solutions = $user->solutions()
            ->versioned()
            ->with('exercise.chapter')
            ->latest('solutions.created_at')
            ->paginate(10)
            ->withQueryString();

        $page = new SolutionListPageData(
            userName: $user->name,
            userUrl: route('users.show', $user),
            progressUrl: route('my.show'),
            items: array_map(SolutionListItemData::fromModel(...), $solutions->items()),
            pagination: PaginationData::fromPaginator($solutions),
        );

        return $this->inertia($page->toArray())
            ->withViewData([
                'robots' => 'noindex, nofollow',
                'description' => __('my.description'),
            ]);
    }
}
