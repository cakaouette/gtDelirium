<?php

namespace App\Middleware;

use Slim\Views\Twig;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class TwigGlobalsMiddleware
{
    private Twig $twig;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig template engine
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Example middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        $this->twig->getEnvironment()->addGlobal('page', new class ($route) {
            var $title;
            var $current;
            var $breadcrumb;
            function __construct($route) {
                $this->title = $route->getArgument('content-title');
                $this->current = $route->getName();
                //TODO
                $this->breadcrumb = [];
            }
        });
        $this->twig->getEnvironment()->addGlobal('user', new class {
            //TODO
            var $isGestion = true;
            var $isOfficier = true;
            var $isJoueur = true;
            var $isConnected = true;
        });
        $this->twig->getEnvironment()->addGlobal('members', new class {
            //TODO
            var $pending = 3;
        });
        return $handler->handle($request);
    }
}