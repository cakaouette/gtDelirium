<?php

namespace App\Controller;

use Exception;
use App\Manager\GuildManager;
use App\Manager\MemberManager;
use App\Manager\PendingManager;
use App\Manager\PermissionManager;
use App\Validator\PasswordValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HomeController extends BaseController
{
    private GuildManager $_guildManager;
    private MemberManager $_memberManager;
    private PendingManager $_pendingManager;
    private PermissionManager $_permissionManager;

    protected function __init($bag) {
        $this->_guildManager = $bag->get(GuildManager::class);
        $this->_memberManager = $bag->get(MemberManager::class);
        $this->_pendingManager = $bag->get(PendingManager::class);
        $this->_permissionManager = $bag->get(PermissionManager::class);
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
            $redirect = $this->connectWith($login, $passwd);
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
                if ($this->addPending($pseudo, $login, $this->_memberManager->cryptPassword($passwd, $login))) {
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
                $member = $this->_memberManager->getByLogin($login);
            } catch (Exception $ex) {
                $member = NULL;
                $this->addMsg("danger", "Login ou mdp incorrect");
            }
            
            if (!is_null($member) and $this->_memberManager->isPasswdCorrect($member, $passwd)) {
                $redirectUrl = $this->session->get("redirectUrl");

                //TODO uncomment when grades are out of the session (security risk)
                //$this->session->destroy();
                //$this->session->start();
                //$this->session->regenerateId();

                $guild = $this->_guildManager->getById($member->getGuildInfo()["id"]);

                $this->session->set("id", $member->getId());
                $this->session->set("login", $login);
                $this->session->set("grade", $this->_permissionManager->getGradeById($member->getPermInfo()["id"]));
                $this->session->set("guild", Array("id" => $guild->getId(), "name" => $guild->getName(), "color" => $guild->getColor()));
                $defaultPage = ['raid-info'];
                if ($this->session->get("grade") <= $this->session->get("Officier"))
                {
                    $defaultPage = ['admin-dashboard'];
                    try {
                        $nb = count($this->_pendingManager->getAll());
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
        if(!is_null($this->_pendingManager->getPendingByPseudo($pseudo))) {
            $this->addMsg("warning", "Vous êtes toujours en attente de validation, "
            . "Si c'est urgent, vous pouvez contacter un admin/chef sur le channel \"Site\" du discord");
            return false;
        }
        try {
            if ($this->_pendingManager->addPending($pseudo, $login, $passwd)) {
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