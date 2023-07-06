<?php

namespace App\SocialAuth\Actions\Admin;

use App\SocialAuth\SocialAuthService;
use ClientX\Actions\Action;
use ClientX\Renderer\RendererInterface;

class IndexAdminAction extends Action
{
    private SocialAuthService $socialAuthService;

    public function __construct(RendererInterface $renderer, SocialAuthService $socialAuthService)
    {
        $this->renderer = $renderer;
        $this->socialAuthService = $socialAuthService;
    }

    public function __invoke()
    {
        $enabled = $this->socialAuthService->findEnabled(false);
        $providers = $this->socialAuthService->getProviders();
        $indb = $this->socialAuthService->findAll();
        return $this->render('@socialauth_admin/index', compact('providers', 'enabled', 'indb'));
    }
}