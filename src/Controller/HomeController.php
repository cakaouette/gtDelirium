<?php

namespace App\Controller;

use Exception;
use App\Validator\PasswordValidator;
use Psr\Http\Message\ResponseInterface;
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

final class HomeController extends BaseController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $this->view->render($response, 'home/index.twig');
    }

    public function connect(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $login = null;
        if ($request->getMethod() === 'POST') {
            $form = $request->getParsedBody();
            $login = $form['loginForm'];
            $passwd = $form['passwdForm'];
            //TODO get it from settings
            require('private/indexPrivate.php');
            $redirect = $this->connectWith($login, md5($passwd.$_SALT.$login));
            if ($redirect !== false) {
                return $this->redirect($response, $redirect);
            }
        }
        return $this->view->render($response, 'home/connect.twig', ['savedLogin' => $login]);
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
            $checkPwd = (new PasswordValidator())->validate($passwd);;
            $errorPasswd = $checkPwd['msg'];
            if (!is_null($pseudo) and !is_null($login) and ($checkPwd['accept'] === true)) {
                //TODO get it from settings
                require('private/indexPrivate.php');
                if ($this->addPending($pseudo, $login, md5($passwd.$_SALT.$login))) {
                    return $this->redirect($response, ['home']);
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
        return $this->redirect($response, ['home']);
    }

    //TODO move out of the controller
    private function connectWith($login, $passwd)
    {
        if (!(is_null($login) or $login == "") and !is_null($passwd)) {
            try {
                //TODO inject in the constructor
                $memberManager = new MemberManager();
                $member = $memberManager->getByLogin($login);
            } catch (Exception $ex) {
                $member = NULL;
                $this->addMsg("danger", "Login ou mdp incorrect");
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
                //$defaultPage = $this->router->urlFor('raid-info');
                if ($this->session->get("grade") <= $this->session->get("Officier"))
                {
                    $defaultPage = '/?page=admin&subpage=dashboard';
                    //$defaultPage = $this->router->urlFor('admin-dashboard');
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
        if(!is_null($pendingManager->getPendingByPseudo($pseudo))) {
            $this->addMsg("warning", "Vous êtes toujours en attente de validation, "
            . "Si c'est urgent, vous pouvez contacter un admin/chef sur le channel \"Site\" du discord");
            return false;
        }
        try {
            if ($pendingManager->addPending($pseudo, $login, $passwd)) {
                $this->addMsg("success", "Pré-inscription réussite");
                $this->addMsg("info", "Patientez le temps qu'un admin valide le compte"
                        . " (ou contacter un admin/chef sur le channel \"Site\" du discord)");
                return false;
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
        return true;
    }


}