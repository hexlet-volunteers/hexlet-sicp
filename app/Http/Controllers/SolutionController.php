<?php

namespace App\Http\Controllers;

use App\DTO\PaginationData;
use App\DTO\SelectOptionData;
use App\DTO\Solution\SolutionFilterData;
use App\DTO\Solution\SolutionListItemData;
use App\DTO\Solution\SolutionListPageData;
use App\DTO\Solution\SolutionShowPageData;
use App\Models\Exercise;
use App\Models\Solution;
use App\Support\Navigation\NavigationBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SolutionController extends Controller
{
    public function index(Request $request, NavigationBuilder $navigation): Response
    {
        $solutions = QueryBuilder::for(Solution::versioned())
            ->allowedFilters(
                AllowedFilter::exact('exercise_id'),
                AllowedFilter::partial('user.name'),
                AllowedFilter::exact('user_id'),
            )
            ->with(['user', 'exercise'])
            ->whereHas('user')
            ->latest('solutions.created_at')
            ->paginate(50)
            ->withQueryString();

        // ponytail: все ~356 упражнений (~15 КБ) в каждом рендере; Inertia::optional, если станет заметно.
        $exercises = Exercise::orderBy('id')->get()->map(
            fn(Exercise $exercise) => new SelectOptionData((string) $exercise->id, $exercise->getFullTitle()),
        );

        // Arr::get, а не input('filter.user.name'): ключ буквально «user.name», точка — не вложенность.
        $filter = (array) $request->input('filter', []);
        $userName = Arr::get($filter, 'user.name');
        $exerciseId = Arr::get($filter, 'exercise_id');

        $page = new SolutionListPageData(
            items: array_map(SolutionListItemData::fromModel(...), $solutions->items()),
            pagination: PaginationData::fromPaginator($solutions),
            // ?filter[user.name][]=… QueryBuilder принимает, а строковое поле DTO — нет.
            filter: new SolutionFilterData(
                userName: is_string($userName) ? $userName : null,
                exerciseId: is_string($exerciseId) ? $exerciseId : null,
            ),
            filterUrl: route('solutions.index'),
            exercises: $exercises->all(),
            tabs: $navigation->exercises(),
        );

        // Публичная и индексируемая: без SSR это принятый SEO-долг (ADR 0004), noindex не ставим.
        return $this->inertia($page->toArray());
    }

    public function show(Solution $solution): Response
    {
        if (!$solution->user()->exists()) {
            abort(404);
        }

        $page = SolutionShowPageData::fromExercise($solution->exercise, $solution->user);

        // Имя компонента явное: User\SolutionController@show рендерит ту же страницу.
        return $this->inertia($page->toArray(), 'Solution/Show')
            ->withViewData(['robots' => 'noindex, nofollow', 'description' => $page->description()]);
    }
}
