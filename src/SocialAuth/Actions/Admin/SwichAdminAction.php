<?php

namespace App\SocialAuth\Actions\Admin;

use App\SocialAuth\SocialAuthService;
use ClientX\Actions\Action;
use ClientX\Middleware\CsrfMiddleware;
use ClientX\Renderer\RendererInterface;
use ClientX\Session\FlashService;
use Psr\Http\Message\ServerRequestInterface;

class SwichAdminAction extends Action
{

    private SocialAuthService $socialAuthService;
    private CsrfMiddleware $csrf;

    public function __construct(RendererInterface $renderer, SocialAuthService $socialAuthService, FlashService $flash, CsrfMiddleware $csrf)
    {
        $this->renderer = $renderer;
        $this->socialAuthService = $socialAuthService;
        $this->csrf = $csrf;
        $this->flash = $flash;
    }
    public function __invoke(ServerRequestInterface $request)
    {

            $name = $request->getAttribute('name');
            $config = $this->socialAuthService->findConfig($name);
            $current = $config->isEnabled();
            $next = !$current;
            $this->socialAuthService->switch($name, $next);
            $csrf = $this->csrf->generateToken();
            $this->success('Done!');
            $data = ['success' => true, 'result' => false, 'csrf' => $csrf];
            return $this->json($data);
    }
}