<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Admin\SolutionListItemData;
use App\DTO\Admin\SolutionListPageData;
use App\DTO\Admin\UserFilterData;
use App\DTO\PaginationData;
use App\Models\Solution;
use App\Support\Navigation\NavigationBuilder;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SolutionController extends AdminController
{
    public function index(Request $request, NavigationBuilder $navigation): Response
    {
        $solutions = QueryBuilder::for(Solution::class)
            ->allowedFilters(
                AllowedFilter::exact('user_id'),
                AllowedFilter::partial('name', 'user.name'),
                AllowedFilter::partial('email', 'user.email'),
            )
            ->with(['user', 'exercise'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $page = new SolutionListPageData(
            items: array_map(SolutionListItemData::fromModel(...), $solutions->items()),
            pagination: PaginationData::fromPaginator($solutions),
            filter: UserFilterData::fromQuery($request),
            filterUrl: route('admin.solutions.index'),
            menu: $navigation->admin($request->only('filter')),
        );

        return $this->inertia($page->toArray())
            ->withViewData(['robots' => 'noindex, nofollow']);
    }
}
