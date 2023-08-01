<?php

namespace App\SocialAuth;

use ClientX\Response\RedirectResponse;
use ClientX\Router;
use ClientX\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ClientX\Helpers\Str;

class SocialAuthMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;
    private Router $router;

    public function __construct(SessionInterface $session, Router $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $this->router->generateURI('socialauth.finish');
        if (Str::startsWith( $request->getUri()->getPath(), ['/theme', '/admin', '/Themes'])){
        return $handler->handle($request);

        }
        if ($this->session->get('socialauth.id') != null && $request->getUri()->getPath() != $path){
            return new RedirectResponse($path);
        }
        return $handler->handle($request);
    }
}
