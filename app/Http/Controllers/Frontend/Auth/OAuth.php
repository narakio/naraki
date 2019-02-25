<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Contracts\Models\User as UserProvider;
use App\Http\Controllers\Frontend\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OAuth extends Controller
{


    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param  string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        return redirect(Socialite::driver($provider)
            ->redirect()->getTargetUrl());
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param  string $provider
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Models\User|\App\Support\Providers\User $userRepo
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider, Request $request, UserProvider $userRepo)
    {
        $socialiteUser = Socialite::driver($provider)->user();

        $user = $userRepo->processViaOAuth($provider, $socialiteUser);

        $request->session()->regenerate();

        \Auth::guard('jwt')->login($user);
        $this->guard()->login($user);

        return redirect()->intended(route_i18n('home'));
    }

}
