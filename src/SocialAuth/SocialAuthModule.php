<?php

namespace App\SocialAuth;

use App\SocialAuth\Actions\Admin\ConfigAdminAction;
use App\SocialAuth\Actions\Admin\IndexAdminAction;
use App\SocialAuth\Actions\Admin\SwichAdminAction;
use App\SocialAuth\Actions\AuthorizeAction;
use App\SocialAuth\Actions\FinishAction;
use App\SocialAuth\Actions\RedirectAction;
use App\SocialAuth\Event\SocialAuthEventManager;
use ClientX\Event\EventManager;
use ClientX\Module;
use ClientX\Renderer\RendererInterface;
use ClientX\Router;
use Psr\Container\ContainerInterface;

class SocialAuthModule extends Module
{
    const DEFINITIONS = __DIR__ . '/config.php';

    public function __construct(Router $router, RendererInterface $renderer, ContainerInterface $container, EventManager $event)
    {
        // Pour désactiver la connexion et le mot de passe oublié des comptes connectés avec un provider
        $event->attach('auth.login', $container->get(SocialAuthEventManager::class));
        $event->attach('auth.password.forgot', $container->get(SocialAuthEventManager::class));
        $renderer->addPath('socialauth_admin', __DIR__ . '/views');
        $router->get('/socialauth/authorize/[*:name]', AuthorizeAction::class, 'socialauth.authorize');
        $router->get('/socialauth/redirect/[*:name]', RedirectAction::class, 'socialauth.redirect');
        $router->get('/socialauth/finish', FinishAction::class, 'socialauth.finish');
        $router->post('/socialauth/finish', FinishAction::class);
        $adminPrefix = $container->get('admin.prefix');
        if ($adminPrefix){
            $router->get($adminPrefix . '/socialauth', IndexAdminAction::class, 'socialauth.admin.index');
            $router->any($adminPrefix . '/socialauth/config/[*:name]', ConfigAdminAction::class, 'socialauth.admin.config');
            $router->post($adminPrefix . '/socialauth/switch/[*:name]', SwichAdminAction::class, 'socialauth.admin.switch');
        }
    }
}