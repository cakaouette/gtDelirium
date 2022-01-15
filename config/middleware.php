<?php

use Slim\App;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Middleware\ErrorMiddleware;
use App\Middleware\TwigGlobalsMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

return function (App $app) {
    //Twig globals
    $app->add(TwigGlobalsMiddleware::class);

    // Twig middlware
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    // Catch exceptions and errors
    $app->add(ErrorMiddleware::class);

    // CSRF middlewre
    $app->add(Guard::class);

    // This should always stay last until no old index routes are used
    $app->add(function (Request $request, RequestHandler $handler) {
        $currentPath = $request->getUri()->getPath();
        if ($currentPath == "/" || $currentPath == "/index.php") {
            ini_set('include_path',ini_get('include_path').':../:');
            require("../index.php");
        }

        return $handler->handle($request);
    });
};