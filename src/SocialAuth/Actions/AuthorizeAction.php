<?php

namespace App\SocialAuth\Actions;

use App\SocialAuth\SocialAuthService;
use ClientX\Actions\Action;
use ClientX\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizeAction extends Action
{

    private SocialAuthService $service;
    private SessionInterface $session;

    public function __construct(SocialAuthService $service, SessionInterface $session)
    {
        $this->service = $service;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        try {
            $provider = $this->service->getProvider($request->getAttribute('name'));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $url = $provider->getAuthorizationUrl();
        $this->session->set('oauth2state', $provider->getState());
        $this->session->set('oauth2pkceCode', $provider->getPkceCode());
        return $this->redirect($url);
    }
}