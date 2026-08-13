<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        session()->put('url.intended', url()->previous());

        return view('auth.login');
    }

    public function redirectTo()
    {
        flash(__('auth.logged_in'))->success();

        return session()->get('url.intended', route('my.show'));
    }

    public function devLogin()
    {
        abort_unless(app()->environment('local'), 404);

        $user = User::admins()->first();

        Auth::login($user);

        return redirect()->back();
    }

    public function logout()
    {
        Auth::logout();
        flash(__('auth.logout'))->success();
        return redirect()->back();
    }
}
