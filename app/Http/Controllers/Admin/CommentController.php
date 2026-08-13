<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CommentController extends AdminController
{
    public function index(Request $request): View
    {
        $comments = QueryBuilder::for(Comment::class)
            ->allowedFilters(
                AllowedFilter::partial('name', 'user.name'),
                AllowedFilter::partial('email', 'user.email'),
            )
            ->with(['user', 'commentable'])
            ->latest()
            ->paginate(50)
            ->appends($request->query());

        return view('admin.comments', compact('comments'));
    }
}
