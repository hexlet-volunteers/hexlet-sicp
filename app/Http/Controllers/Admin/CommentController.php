<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Admin\CommentListItemData;
use App\DTO\Admin\CommentListPageData;
use App\DTO\Admin\UserFilterData;
use App\DTO\PaginationData;
use App\Models\Comment;
use App\Support\Navigation\NavigationBuilder;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CommentController extends AdminController
{
    public function index(Request $request, NavigationBuilder $navigation): Response
    {
        $comments = QueryBuilder::for(Comment::class)
            ->allowedFilters(
                AllowedFilter::partial('name', 'user.name'),
                AllowedFilter::partial('email', 'user.email'),
            )
            ->with(['user', 'commentable'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $page = new CommentListPageData(
            items: array_map(CommentListItemData::fromModel(...), $comments->items()),
            pagination: PaginationData::fromPaginator($comments),
            filter: UserFilterData::fromQuery($request),
            filterUrl: route('admin.comments.index'),
            menu: $navigation->admin($request->only('filter')),
        );

        return $this->inertia($page->toArray())
            ->withViewData(['robots' => 'noindex, nofollow']);
    }
}
