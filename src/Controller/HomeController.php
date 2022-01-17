<?php

namespace App\Controller;

use Exception;
use Slim\Views\Twig;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Psr\Http\Message\ServerRequestInterface;

//TODO use namespace and use instead of require once migration is over
require_once('model/Manager/MemberManager.php');
use MemberManager;
require_once('model/Manager/GuildManager.php');
use GuildManager;
require_once('model/Manager/PendingManager.php');
use PendingManager;
require_once('model/Manager/RaidManager.php');
use RaidManager;
require_once('model/Manager/PermissionManager.php');
use PermissionManager;

final class HomeController
{
    private Twig $view;
    private SessionInterface $session;
    private RouteParserInterface $router;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig template engine
     */
    public function __construct(Twig $twig, SessionInterface $session, RouteParserInterface $router) {
        $this->view = $twig;
        $this->session = $session;
        $this->router = $router;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $this->view->render($response, 'home/index.twig');
    }

    public function connect(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $login = null;
        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $login = $form['loginForm'];
            $passwd = $form['passwdForm'];
            $checkPwd = $this->checkPasswd($passwd);
            if (!$checkPwd['accept']) {
                return $response->withStatus(302)->withHeader('Location', $this->routeParser->urlFor('register'));
            }
            //TODO get it from settings
            require('private/indexPrivate.php');
            $redirect = $this->connectWith($login, md5($passwd.$_SALT.$login));
            if ($redirect !== false) {
                return $response->withStatus(302)->withHeader('Location', $redirect);
            }
        }
        return $this->view->render($response, 'home/connect.twig', [
            'savedLogin' => $login
        ]);
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $errorPasswd = null;
        $login = null;
        $pseudo = null;
        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $login = $form['loginForm'];
            $pseudo = $form['pseudoForm'];
            $passwd = $form['passwdForm'];
            $checkPwd = $this->checkPasswd($passwd);
            $errorPasswd = $checkPwd['msg'];
            if (!is_null($pseudo) and !is_null($login) and ($checkPwd['accept'] === true)) {
                //TODO get it from settings
                require('private/indexPrivate.php');
                if ($this->addPending($pseudo, $login, md5($passwd.$_SALT.$login))) {
                    return $response->withStatus(302)->withHeader('Location', $this->route->urlFor('home'));
                }
            }
        }
        return $this->view->render($response, 'home/register.twig', [
            'savedLogin' => $login,
            'errorPasswd' => $errorPasswd,
            'savedPseudo' => $pseudo
        ]);
    }

    public function disconnect(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $this->session->destroy();
        return $response->withStatus(302)->withHeader('Location', $this->route->urlFor('home'));
    }

    private function checkPasswd($passwd): array {
        if (is_null($passwd)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe non vide");
        }
        if (!preg_match("~.{10,}~", $passwd)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe d'au moins 10 caractères");
        }
        if (!preg_match("~[a-z]~", $passwd)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe avec au moins une lettre minuscule");
        }
        if (!preg_match("~[A-Z]~", $passwd)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe avec au moins une lettre Majuscule");
        }
        if (!preg_match("~\\d+~", $passwd)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe avec au moins un chiffre");
        }
        return array("accept" => true, "msg" => "");
    }

    //TODO move out of the controller
    private function connectWith($login, $passwd)
    {
        if (!(is_null($login) or $login == "") and !is_null($passwd)) {
            $flash = $this->session->getFlash();
            try {
                //TODO inject in the constructor
                $memberManager = new MemberManager();
                $member = $memberManager->getByLogin($login);
            } catch (Exception $ex) {
                $member = NULL;
                $flash->add("danger", "Login ou mdp incorrect");
            }
            
            if (!is_null($member) and ($member->getPasswd() == $passwd)) {
                $redirectUrl = $this->session->get("redirectUrl");

                //TODO uncomment when grades are out of the session (security risk)
                //$this->session->destroy();
                //$this->session->start();
                //$this->session->regenerateId();

                $permissionManager = new PermissionManager();
                $guild = (new GuildManager())->getById($member->getGuildInfo()["id"]);

                $this->session->set("id", $member->getId());
                $this->session->set("login", $login);
                $this->session->set("grade", $permissionManager->getGradeById($member->getPermInfo()["id"]));
                $this->session->set("guild", Array("id" => $guild->getId(), "name" => $guild->getName(), "color" => $guild->getColor()));
                //TODO replace url when the page is finished
                $defaultPage = '/index.php?page=raid&subpage=info';
                //$defaultPage = $this->route->urlFor('raid-info');
                if ($this->session->get("grade") <= $this->session->get("Officier"))
                {
                    $defaultPage = '/?page=admin&subpage=dashboard';
                    //$defaultPage = $this->route->urlFor('admin-dashboard');
                    try {
                        $nb = count((new PendingManager())->getAll());
                        $this->session->set("nbPending", $nb);
                    } catch (Exception $ex) {
                    }
                }
                $redirectPage = $redirectUrl ?? $defaultPage;
                if ($this->session->has('redirectUrl')) $this->session->remove("redirectUrl");
                return $redirectPage;
            }
        }
        return false;
    }

    //TODO move out of the controller
    private function addPending($pseudo, $login, $passwd)
    {
        //TODO inject in the constructor
        $pendingManager = new PendingManager();
        $flash = $this->session->getFlash();
        if(!is_null($pendingManager->getPendingByPseudo($pseudo))) {
            $flash->add("warning", "Vous êtes toujours en attente de validation, "
            . "Si c'est urgent, vous pouvez contacter un admin/chef sur le channel \"Site\" du discord");
            return false;
        }
        try {
            if ($pendingManager->addPending($pseudo, $login, $passwd)) {
                $flash->add("success", "Pré-inscription réussite");
                $flash->add("info", "Patientez le temps qu'un admin valide le compte"
                        . " (ou contacter un admin/chef sur le channel \"Site\" du discord)");
                return false;
            }
        } catch (Exception $e) {
            $flash->add("danger", $e->getMessage());
        }
        return true;
    }


}