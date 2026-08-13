<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserCommentController extends Controller
{
    public function index(Request $request, User $user): View
    {
        $comments = $user->comments()->with('commentable', 'user')->get();
        return view('user.comment.index', compact(
            'comments'
        ));
    }
}
