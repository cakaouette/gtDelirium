<?php

namespace App\Controller;

use Slim\Views\Twig;
use App\Manager\ManagerBag;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Laminas\Config\Config;

class BaseController
{
    protected Twig $view;
    protected SessionInterface $session;
    protected RouteParserInterface $router;
    protected $configDirPath;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig template engine
     */
    public function __construct(Twig $twig,
                                SessionInterface $session,
                                RouteParserInterface $router,
                                ManagerBag $bag,
                                Config $config) {
        $this->view = $twig;
        $this->session = $session;
        $this->router = $router;
        $this->__init($bag);
        $this->configDirPath = $config->get('dirPath');
    }

    protected function __init($bag) {}

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