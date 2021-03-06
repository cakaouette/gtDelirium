<?php

namespace App\Controller;

use Slim\Views\Twig;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;

class BaseController
{
    protected Twig $view;
    protected SessionInterface $session;
    protected RouteParserInterface $router;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig template engine
     */
    public function __construct(Twig $twig, SessionInterface $session, RouteParserInterface $router) {
        $this->view = $twig;
        $this->session = $session;
        $this->router = $router;
        $this->__init();
    }

    protected function __init() {}

    protected function addMsg(string $type, string $message) {
        $this->session->getFlash()->add($type, $message);
    }

    protected function redirect(ResponseInterface $response, string|array $args) {
        $url = $args;
        if (is_array($args))
            $url = $this->router->urlFor(...$args);
        return $response->withStatus(302)->withHeader('Location', $url);
    }
}