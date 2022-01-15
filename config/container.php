<?php

use Slim\App;
use Monolog\Logger;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
use Monolog\ErrorHandler;
use Slim\Factory\AppFactory;
use Twig\Extra\Html\HtmlExtension;
use Slim\Middleware\ErrorMiddleware;
use Psr\Container\ContainerInterface;
use Monolog\Handler\RotatingFileHandler;
use App\Middleware\TwigGlobalsMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $logger = $container->get(Logger::class);
        $settings = $container->get('settings')['error'];

        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details'],
            $logger
        );
    },

    Guard::class => function (ContainerInterface $container) {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        session_start();
        return new Guard($responseFactory);
    },

    Twig::class => function (ContainerInterface $container) {
        $twig = Twig::create('../templates', ['cache' => false]);
        $twig->addExtension(new HtmlExtension());
        return $twig;
    },

    TwigGlobalsMiddleware::class => function (ContainerInterface $container) {
        $twig = $container->get(Twig::class);
        return new TwigGlobalsMiddleware($twig);
    },

    Logger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new Logger($settings['name']);
        ErrorHandler::register($logger);
        $logger->pushHandler(new RotatingFileHandler($settings['file'], 0, $settings['level'], true, $settings['file_permission']));
        return $logger;
    }
];