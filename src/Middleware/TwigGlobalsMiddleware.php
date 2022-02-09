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
        $env->addGlobal('page', [
            'title' => $route->getArgument('content-title'),
            'current' => $route->getName(),
            'breadcrumb' => []
        ]);
        $env->addGlobal('user', [
            //TODO change when we remove grades from session
            'isGestion' => $this->session->get("grade") <= $this->session->get("Gestion"),
            'isOfficier' => $this->session->get("grade") <= $this->session->get("Officier"),
            'isJoueur' => $this->session->get("grade") <= $this->session->get("Joueur"),
            'isConnected' => $this->session->get("grade") < $this->session->get("Visiteur"),
            'id' => $this->session->get("id")
        ]);
        $env->addGlobal('guild', $this->session->has("guild") ? [
            'id' => $this->session->get("guild")["id"],
            'name' => $this->session->get("guild")["name"],
            'color' => $this->session->get("guild")["color"]
        ] : []);
        $env->addGlobal('members', ['pending' => $this->session->get('nbPending')]);
        $env->addGlobal('flash', $this->session->getFlash());
        $env->addGlobal('csrf', new class ($this->csrf, $request) {
            var $nameKey;
            var $valueKey;
            var $name;
            var $value;
            function __construct($csrf, $request) {
                //TODO uncomment after solving POST issue
                //if ($request->getMethod() === 'GET')  {
                    $this->nameKey = $csrf->getTokenNameKey();
                    $this->valueKey = $csrf->getTokenValueKey();
                    $this->name = $request->getAttribute($this->nameKey);
                    $this->value = $request->getAttribute($this->valueKey);
                //}
            }
        });
        return $handler->handle($request);
    }
}