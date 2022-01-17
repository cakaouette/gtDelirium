<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ErrorController
{
    private Twig $view;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig template engine
     */
    public function __construct(Twig $twig) {
        $this->view = $twig;
    }

    public function x403(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $this->view->render($response, 'error.twig');
    }
}