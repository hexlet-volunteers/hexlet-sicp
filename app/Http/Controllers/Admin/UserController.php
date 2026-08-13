<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Admin\UpdateUserData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends AdminController
{
    public function index(Request $request): View
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
            )
            ->latest()
            ->paginate(50)
            ->appends($request->query());

        return view('admin.users', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserData $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'github_name' => $request->github_name,
            'is_admin' => $request->is_admin,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated');
    }
}
