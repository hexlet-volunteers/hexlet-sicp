<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Admin\UpdateUserData;
use App\DTO\Admin\UserEditPageData;
use App\DTO\Admin\UserFilterData;
use App\DTO\Admin\UserListItemData;
use App\DTO\Admin\UserListPageData;
use App\DTO\PaginationData;
use App\Models\User;
use App\Support\Navigation\NavigationBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends AdminController
{
    public function index(Request $request, NavigationBuilder $navigation): Response
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
            )
            ->latest()
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $page = new UserListPageData(
            items: array_map(UserListItemData::fromModel(...), $users->items()),
            pagination: PaginationData::fromPaginator($users),
            filter: UserFilterData::fromQuery($request),
            filterUrl: route('admin.users.index'),
            menu: $navigation->admin($request->only('filter')),
        );

        return $this->inertia($page->toArray())
            ->withViewData(['robots' => 'noindex, nofollow']);
    }

    public function edit(User $user, NavigationBuilder $navigation): Response
    {
        $page = new UserEditPageData(
            name: $user->name,
            githubName: $user->github_name,
            isAdmin: (bool) $user->is_admin,
            updateUrl: route('admin.users.update', $user),
            cancelUrl: route('admin.users.index'),
            menu: $navigation->admin(),
        );

        return $this->inertia($page->toArray())
            ->withViewData(['robots' => 'noindex, nofollow']);
    }

    public function update(UpdateUserData $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->name,
            'github_name' => $request->github_name,
            // Как у HTML-чекбокса: нет поля — не админ. Иначе NULL в NOT NULL-колонке.
            'is_admin' => (bool) $request->is_admin,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', __('layout.flash.success'));
    }
}
