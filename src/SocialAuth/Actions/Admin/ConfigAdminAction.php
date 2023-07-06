<?php

namespace App\SocialAuth\Actions\Admin;

use App\SocialAuth\SocialAuthService;
use ClientX\Actions\Action;
use ClientX\Helpers\RequestHelper;
use ClientX\Renderer\RendererInterface;
use ClientX\Router;
use ClientX\Session\FlashService;
use ClientX\Validator;
use Psr\Http\Message\ServerRequestInterface;

class ConfigAdminAction extends Action
{

    private SocialAuthService $socialAuthService;

    public function __construct(RendererInterface $renderer, SocialAuthService $socialAuthService, FlashService $flash, Router $router)
    {
        $this->renderer = $renderer;
        $this->socialAuthService = $socialAuthService;
        $this->flash = $flash;
        $this->router = $router;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $name = $request->getAttribute('name');
        $provider = $this->socialAuthService->getProvider($name, false);
        $config = $this->socialAuthService->findConfig($name);
        $redirectUri = RequestHelper::getDomain($request) . '/socialauth/redirect/' . $name;
        if ($request->getMethod() == 'GET'){
            return $this->render('@socialauth_admin/config', compact('config','provider', 'name', 'redirectUri'));
        }
        $params = $request->getParsedBody();
        $validate = $this->validate($params);
        if($validate->isValid()){
            $this->socialAuthService->saveProvider($name, $params['client_id'], $params['client_secret'], $redirectUri);
            $this->success('Done!');
            return $this->redirectToRoute('socialauth.admin.index');
        }
    }

    private function validate(array $params)
    {
        return (new Validator($params))
            ->notEmpty('client_id', 'client_secret');
    }
}