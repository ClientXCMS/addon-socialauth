<?php

namespace App\SocialAuth;

use ClientX\Response\RedirectResponse;
use ClientX\Router;
use ClientX\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

        if ($this->session->get('socialauth.id') != null && $request->getUri()->getPath() != $path){
            return new RedirectResponse($path);
        }
        return $handler->handle($request);
    }
}