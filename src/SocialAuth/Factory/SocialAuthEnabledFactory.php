<?php

namespace App\SocialAuth\Factory;

use App\SocialAuth\SocialAuthService;
use Psr\Container\ContainerInterface;

class SocialAuthEnabledFactory
{

    public function __invoke(ContainerInterface $container)
    {
        return $container->get(SocialAuthService::class)->findEnabled();
    }
}