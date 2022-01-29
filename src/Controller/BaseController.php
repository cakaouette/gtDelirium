<?php

namespace App\Controller;

use Slim\Views\Twig;
use Odan\Session\SessionInterface;
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
}