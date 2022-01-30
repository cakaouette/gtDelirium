<?php

namespace App\Middleware;

use Exception;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteParserInterface;

require_once('controller/Raid/RaidControler.php');
use RaidControler;
require_once('model/Manager/PermissionManager.php');
use PermissionManager;

//To add to routes that need auth
final class CoexistenceMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private SessionInterface $session;
    private RouteParserInterface $router;

    public function __construct(ResponseFactoryInterface $responseFactory, SessionInterface $session, RouteParserInterface $router) {
        $this->responseFactory = $responseFactory;
        $this->session = $session;
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{
        $newPath = $this->getNewPath($request);
        if ($newPath === false) {
            require("../index.php");
            exit;
        }
        //error_reporting(E_ERROR);

        if ($newPath !== true) {
            return $this->responseFactory->createResponse()->withStatus(302)->withHeader('Location', $newPath);
        }

        //To keep the app working in both, the current session info need to be initialized this way
        if (!$this->session->isStarted()) {
            $this->session->start();
            if (!$this->session->has('login')) {
                $permissionManager = new PermissionManager();
                $this->session->set("login", NULL);
                $this->session->set("id", NULL);
                $this->session->set("redirectUrl", NULL);
                $this->session->set("grade", $permissionManager->getGradeByName("Visiteur"));
                $this->session->set("guild", Array("id" => NULL, "name" => NULL));
                $this->session->set("nbPending", NULL);
                try {
                    RaidControler::setGuildRaidInfo(NULL, NULL);
                } catch (Exception $e) {
                    $this->session->set("raidPreview", Array("id" => NULL,
                                                "dateRaid" => NULL));
                    $this->session->set("raidInfo", Array("id" => NULL,
                                                "dateRaid" => NULL,
                                                "dateNumber" => NULL,
                                                "isFinished" => NULL));
                }

                foreach ($permissionManager->getAllInRawData() as $permId =>$permInfo) {
                    $this->session->set($permInfo["name"], $permInfo["grade"]);
                }
            }
        }

        return $handler->handle($request);
    }

    private function getNewPath($request) {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $query = $uri->getQuery();
        if ($path == "/" || $path == "/index.php") {
            switch ($_GET['page']) {
                case 'connect':
                    return $this->router->urlFor('connect');
                    break;
                case 'profile':
                    return $this->router->urlFor('my-profile');
                    break;
                case 'boss':
                    return $this->router->urlFor('bosses');
                    break;
                case 'tip':
                    return $this->router->urlFor('tip');
                    break;
                case 'conquest':
                    return $this->router->urlFor('conquest-' . $_GET['subpage']);
                    break;
                case 'alliance':
                    return $this->router->urlFor('alliance');
                    break;
            }
            return false;
        }
        return true;
    }
}