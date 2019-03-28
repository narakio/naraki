<?php namespace Naraki\Oauth\Contracts;

use App\Models\User as UserModel;
use Laravel\Socialite\Two\User as SocialiteUser;

interface OauthProcessable
{
    public function processViaOAuth(string $provider, SocialiteUser $socialiteUser): UserModel;
}