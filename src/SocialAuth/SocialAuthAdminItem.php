<?php

namespace App\SocialAuth;

use ClientX\Navigation\NavigationItemInterface;
use ClientX\Renderer\RendererInterface;

class SocialAuthAdminItem implements NavigationItemInterface
{

    /**
     * @inheritDoc
     */
    public function getPosition(): int
    {
        return 80;
    }

    /**
     * @inheritDoc
     */
    public function render(RendererInterface $renderer): string
    {
        return $renderer->render("@socialauth_admin/menu");
    }
}