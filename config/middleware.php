<?php

use Slim\App;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Middleware\ErrorMiddleware;
use App\Middleware\TwigGlobalsMiddleware;
use App\Middleware\CoexistenceMiddleware;
use Odan\Session\Middleware\SessionMiddleware;

return function (App $app) {
    //Twig globals
    $app->add(TwigGlobalsMiddleware::class);

    // Twig middlware
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

    // CSRF middlewre
    $app->add(Guard::class);

    // Start the session
    $app->add(SessionMiddleware::class);

    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    // Catch exceptions and errors
    $app->add(ErrorMiddleware::class);

    // This should always stay last until no old index routes are used
    $app->add(CoexistenceMiddleware::class);
};