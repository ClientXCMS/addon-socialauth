<?php

namespace App\SocialAuth\Actions;

use App\SocialAuth\SocialAuthService;
use ClientX\Actions\Action;
use ClientX\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class RedirectAction extends Action
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

        $provider->setPkceCode($this->session->get('oauth2pkceCode'));
        $sessionState = $this->session->get('oauth2state');
        $state = $request->getQueryParams()['state'];
        if (empty($state) ||empty($sessionState) || $state !== $sessionState){
            die("Invalid state");
        }
        try {

            $token = $provider->getAccessToken('authorization_code', [
                'code' => $request->getQueryParams()['code']
            ]);
            $user = $provider->getResourceOwner($token);
            return $this->service->authorize($user, $token, $request->getAttribute('name'));
        } catch (\Exception $e) {

            // Failed to get user details
            exit('Internal error. Please retry log');

        }

        return '';
    }
}