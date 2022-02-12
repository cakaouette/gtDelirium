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
                case 'profile':
                    return $this->router->urlFor('my-profile');
                case 'boss':
                    return $this->router->urlFor('bosses');
                case 'tip':
                    return $this->router->urlFor('tip');
                case 'conquest':
                    return $this->router->urlFor('conquest-' . $_GET['subpage']);
                case 'alliance':
                    return $this->router->urlFor('alliance');
                case 'admin':
                    return $this->router->urlFor('admin-dashboard');
                case 'member':
                    return $this->router->urlFor('member-alliance');
                case 'raid':
                    switch ($_GET['subpage']) {
                        case 'info':
                            return $this->router->urlFor('raid-info');
                        case 'rank':
                            return $this->router->urlFor('raid-rank');
                        case 'meteo':
                            return $this->router->urlFor('raid-meteo');
                        case 'followup':
                            return $this->router->urlFor('raid-followup');
                    }
                    break;
                default:
                    break;
            }
            return false;
        }
        return true;
    }
}