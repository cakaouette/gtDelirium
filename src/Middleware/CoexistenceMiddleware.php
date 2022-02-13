<?php

namespace App\Middleware;

use Exception;
use App\Manager\RaidManager;
use App\Manager\PermissionManager;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteParserInterface;

final class CoexistenceMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private SessionInterface $session;
    private RouteParserInterface $router;
    private RaidManager $raids;
    private PermissionManager $permissions;

    public function __construct(ResponseFactoryInterface $responseFactory, SessionInterface $session,
        RouteParserInterface $router, PermissionManager $permissions, RaidManager $raids) {
        $this->responseFactory = $responseFactory;
        $this->session = $session;
        $this->router = $router;
        $this->permissions = $permissions;
        $this->raids = $raids;
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
                $this->session->set("login", NULL);
                $this->session->set("id", NULL);
                $this->session->set("redirectUrl", NULL);
                $this->session->set("grade", $this->permissions->getGradeByName("Visiteur"));
                $this->session->set("guild", Array("id" => NULL, "name" => NULL));
                $this->session->set("nbPending", NULL);
                try {
                    $this->setGuildRaidInfo();
                } catch (Exception $e) {
                    $this->session->set("raidInfo", Array("id" => NULL,
                                                "dateRaid" => NULL,
                                                "dateNumber" => NULL,
                                                "isFinished" => NULL));
                }

                foreach ($this->permissions->getAllInRawData() as $permId =>$permInfo) {
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
            if (!isset($_GET['page'])) return $this->route->urlFor('home');
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
                    return $this->router->urlFor('raid-info');
                default:
                    break;
            }
            return false;
        }
        return true;
    }

    private function setGuildRaidInfo()
    {
        $now = time();
        if (is_null($raidId)) {
            $raid = $this->raids->getLastByDate();
            $raidId = $raid->getId();
            $raidDate = $raid->getDate();
        }
        $dPreview = $this->raids->getPreviewDate();
        if (!is_null($dPreview)) {
            $this->session->set('raidPreview', [
                'id' => $dPreview->getId(),
                'dateRaid' => $dPreview->getDate()
            ]);
        } else {
            $this->sessions->set('raidPreview', ['id' => NULL, 'dateRaid' => NULL]);
        }
        $date2 = strtotime($raidDate);
        $diff = max(($now - $date2), 0);
        $dayNumber = floor((($diff / 60) / 60 ) / 24);
        $this->session->set('raidInfo', [
            'id' => $raidId,
            'dateRaid' => $raidDate,
            'dateNumber' => min($dayNumber, 13),
            'isFinished' => $dayNumber > 13
        ]);
    }
}