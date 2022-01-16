<?php

namespace App\Middleware;

use Slim\Csrf\Guard;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class TwigGlobalsMiddleware
{
    private Twig $twig;
    private Guard $csrf;
    private SessionInterface $session;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig template engine
     */
    public function __construct(Twig $twig, SessionInterface $session, Guard $csrf)
    {
        $this->twig = $twig;
        $this->csrf = $csrf;
        $this->session = $session;
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
        $env = $this->twig->getEnvironment();
        $env->addGlobal('page', new class ($route) {
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
        $env->addGlobal('user', new class ($this->session) {
            var $isGestion;
            var $isOfficier;
            var $isJoueur;
            var $isConnected;
            function __construct($session) {
                //TODO change when we remove grades from session
                $this->isGestion = $session->get("grade") <= $session->get("Gestion");
                $this->isOfficier = $session->get("grade") <= $session->get("Officier");
                $this->isJoueur = $session->get("grade") <= $session->get("Joueur");
                $this->isConnected = $session->get("grade") < $session->get("Visiteur");
            }
        });
        $env->addGlobal('members', new class ($this->session) {
            var $pending;
            function __construct($session) {
                $this->pending = $session->get('nbPending');
            }
        });
        $env->addGlobal('flash', $this->session->getFlash());
        $env->addGlobal('csrf', new class ($this->csrf, $request) {
            var $nameKey;
            var $valueKey;
            var $name;
            var $value;
            function __construct($csrf, $request) {
                if ($request->getMethod() === 'GET')  {
                    $this->nameKey = $csrf->getTokenNameKey();
                    $this->valueKey = $csrf->getTokenValueKey();
                    $this->name = $request->getAttribute($this->nameKey);
                    $this->value = $request->getAttribute($this->valueKey);
                }
            }
        });
        return $handler->handle($request);
    }
}