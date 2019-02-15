<?php namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Frontend\Controller;
use App\Support\Frontend\Breadcrumbs;
use App\Support\Providers\User as UserProvider;

class Notifications extends Controller
{
    /**
     * @param \App\Contracts\Models\User|\App\Support\Providers\User $userProvider
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(UserProvider $userProvider)
    {
        $user = auth()->user();
        return view('frontend.site.settings.panes.notifications', [
            'user' => $user,
            'breadcrumbs' => Breadcrumbs::render([
                ['label' => trans('titles.routes.notifications'), 'url' => route_i18n('notifications')]
            ]),
            'avatars' => $userProvider->getAvatars($user->getKey())
        ]);
    }

}