<?php

namespace App\Http\Controllers\Admin;

use App\Models\Solution;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SolutionController extends AdminController
{
    public function index(Request $request): View
    {
        $solutions = QueryBuilder::for(Solution::class)
            ->allowedFilters(
                AllowedFilter::exact('user_id'),
                AllowedFilter::partial('name', 'user.name'),
                AllowedFilter::partial('email', 'user.email'),
            )
            ->with(['user', 'exercise'])
            ->latest()
            ->paginate(50)
            ->appends($request->query());

        return view('admin.solutions', compact('solutions'));
    }
}
