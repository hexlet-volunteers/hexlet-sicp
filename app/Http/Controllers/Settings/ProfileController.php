<?php

namespace App\Http\Controllers\Settings;

use App\DTO\Settings\ProfilePageData;
use App\DTO\Settings\ProfileUpdateData;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Support\Navigation\NavigationBuilder;
use Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(NavigationBuilder $navigation): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $page = new ProfilePageData(
            name: $user->name,
            email: $user->email,
            github_name: $user->github_name,
            profileImage: $user->present()->getProfileImageLink(),
            updateUrl: route('settings.profile.update', $user),
            menu: $navigation->settings(),
        );

        return $this->inertia($page->toArray(), scope: 'settings.profile')
            ->withViewData(['robots' => 'noindex, nofollow']);
    }

    public function update(ProfileUpdateData $data): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->name = $data->name;
        $user->github_name = $data->github_name;

        $flash = $user->save()
            ? ['success' => __('account.account_updated')]
            : ['error' => __('layout.flash.error')];

        return redirect()->route('settings.profile.index')->with($flash);
    }
}
