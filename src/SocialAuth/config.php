<?php

use App\SocialAuth\Factory\SocialAuthEnabledFactory;
use App\SocialAuth\Providers\Discord\DiscordSocialAuthProvider;
use App\SocialAuth\Providers\FaceBook\FaceBookSocialAuthProvider;
use App\SocialAuth\Providers\Github\GithubSocialAuthProvider;
use App\SocialAuth\Providers\Google\GoogleSocialAuthProvider;
use App\SocialAuth\SocialAuthAdminItem;
use function ClientX\setting;
use function DI\add;
use function DI\autowire;
use function DI\factory;
use function DI\get;

return [
    'socialauth.providers' => [new GoogleSocialAuthProvider(), new DiscordSocialAuthProvider(), new GithubSocialAuthProvider(), new FaceBookSocialAuthProvider()],
    'socialauth.enabled' => factory(SocialAuthEnabledFactory::class),
    "admin.menu.items" => add(get(SocialAuthAdminItem::class)),

    \App\SocialAuth\SocialAuthService::class => autowire()
        ->constructorParameter('providers', get('socialauth.providers'))

        ->constructorParameter('banned', setting('banned.emails', ''))
        ->constructorParameter("locale", get('app.locale'))
        ->constructorParameter('tosLinks', setting('tosLinks', '')),
];