<?php

use Slim\App;
use Monolog\Logger;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
use Monolog\ErrorHandler;
use Laminas\Config\Config;
use Slim\Factory\AppFactory;
use Odan\Session\PhpSession;
use Twig\Extra\Html\HtmlExtension;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Odan\Session\SessionInterface;
use Slim\Middleware\ErrorMiddleware;
use Psr\Container\ContainerInterface;
use Monolog\Handler\RotatingFileHandler;
use App\Middleware\TwigGlobalsMiddleware;
use Slim\Interfaces\RouteParserInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Odan\Session\Middleware\SessionMiddleware;
use Slim\Handlers\Strategies\RequestResponseArgs;

return [
    Config::class => function () {
        return new Config(require __DIR__ . '/settings.php');
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponseArgs());
        return $app;
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $logger = $container->get(Logger::class);
        $settings = $container->get(Config::class)->get('error');

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
        $session = $container->get(SessionInterface::class);
        if (!$session->has('csrf') || $session->get('csrf') === null) $session->set('csrf', new CsrfStorage());
        $storage = $session->get('csrf');
        return (new Guard($responseFactory, 'csrf', $storage->value))->setPersistentTokenMode(true);
    },

    Twig::class => function (ContainerInterface $container) {
        $twig = Twig::create('../templates', ['cache' => false]);//'../cache' in prod
        $twig->addExtension(new HtmlExtension());
        $twig->addExtension(new MarkdownExtension());
        $twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load($class) {
                if (MarkdownRuntime::class === $class) {
                    return new MarkdownRuntime(new DefaultMarkdown());
                }
            }
        });
        return $twig;
    },

    TwigGlobalsMiddleware::class => function (ContainerInterface $container) {
        $twig = $container->get(Twig::class);
        $csrf = $container->get(Guard::class);
        $session = $container->get(SessionInterface::class);
        return new TwigGlobalsMiddleware($twig, $session, $csrf);
    },

    Logger::class => function (ContainerInterface $container) {
        $settings = $container->get(Config::class)->get('logger');
        $logger = new Logger($settings['name']);
        ErrorHandler::register($logger);
        $logger->pushHandler(new RotatingFileHandler($settings['file'], 0, $settings['level'], true, $settings['file_permission']));
        return $logger;
    },

    SessionInterface::class => function (ContainerInterface $container) {
        $settings = $container->get(Config::class)->get('session');
        $session = new PhpSession();
        $session->setOptions($settings->toArray());

        return $session;
    },

    SessionMiddleware::class => function (ContainerInterface $container) {
        return new SessionMiddleware($container->get(SessionInterface::class));
    },

    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    }
];

class CsrfStorage {
    var $value = [];
}