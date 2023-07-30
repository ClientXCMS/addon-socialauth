<?php

use App\SocialAuth\Actions\FinishAction;
use App\SocialAuth\Factory\SocialAuthEnabledFactory;
use App\SocialAuth\Providers\Discord\DiscordSocialAuthProvider;
use App\SocialAuth\Providers\FaceBook\FaceBookSocialAuthProvider;
use App\SocialAuth\Providers\GitHub\GithubSocialAuthProvider;
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
    'permissions.list' => add(['socialauth.admin.index' => 'Social Auth']),
    FinishAction::class => autowire()->constructorParameter('options', get('how_did_you_find_us')),
    \App\SocialAuth\SocialAuthService::class => autowire()
        ->constructorParameter('providers', get('socialauth.providers'))

        ->constructorParameter('banned', setting('banned.emails', ''))
        ->constructorParameter("locale", get('app.locale'))
        ->constructorParameter('tosLinks', setting('tosLinks', '')),
];
