<?php

namespace App\Middleware;

use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteParserInterface;

//To add to routes that need auth
final class UserAuthMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    
    private SessionInterface $session;

    private RouteParserInterface $router;

    public function __construct(ResponseFactoryInterface $responseFactory, SessionInterface $session, RouteParserInterface $router) {
        $this->responseFactory = $responseFactory;
        $this->session = $session;
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{
        if ($this->session->has('login')) {
            // User is logged in
            //TODO redirect to 403 if not authorized
            return $handler->handle($request);
        }

        $this->session->set('redirectUrl', $request->getRequestTarget());

        // User is not logged in. Redirect to login page.
        return $this->responseFactory->createResponse()
            ->withStatus(302)
            ->withHeader('Location', $router->urlFor('connect'));
    }
}