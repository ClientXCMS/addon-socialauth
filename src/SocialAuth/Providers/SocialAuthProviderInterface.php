<?php

namespace App\SocialAuth\Providers;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

interface SocialAuthProviderInterface
{
    public function hex():string;
    public function name():string;
    public function title():string;
    public function logo():string;
    public function icon():string;
}