<?php
include_once('AbstractControler.php');

include_once('model/Manager/PendingManager.php');
include_once('model/Manager/PermissionManager.php');
include_once('model/Manager/MemberManager.php');

class ConnectControler extends AbstractControler
{
    private $_pendingManager;
    private $_memberManager;

    public function __construct() {
        parent::__construct("Connexion");
        $this->_pendingManager = new PendingManager();
        $this->_memberManager = new MemberManager();
    }

    public function checkPasswd($passwd): array {
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

    public function addPending($pseudo, $login, $passwd)
    {
        $pendingManager = new PendingManager();
        if(!is_null($pendingManager->getPendingByPseudo($pseudo))) {
          $this->addMsg("warning", "Vous êtes toujours en attente de validation, "
                  . "Si c'est urgent, vous pouvez contacter un admin/chef sur le channel \"Site\" du discord");
          $v_content_header = "Enregistrement";
          $v_title = $this->getTitle();
          $v_msgs = $this->getMsgs();
          require('view/template.php');
          exit();
        }
        try {
            if ($pendingManager->addPending($pseudo, $login, $passwd)) {
                $this->addMsg("success", "Pré-inscription réussite");
                $this->addMsg("info", "Patientez le temps qu'un admin valide le compte"
                        . " (ou contacter un admin/chef sur le channel \"Site\" du discord)");
                $v_content_header = "Enregistrement";
                $v_title = $this->getTitle();
                $v_msgs = $this->getMsgs();
                require('view/template.php');
                exit();
            }
        } catch (Exception $e) {
            $this->addMsg("danger", $e->getMessage());
        }
        header('Location: index.php');
    }

    public function connect($isFirstConnection, $login, $passwd, $pseudo, $errorPasswd = "")
    {
        $_isFirstConnection = $isFirstConnection;
        $_errorPasswd = $errorPasswd;
        $_savedLogin = $login;
        $_savedPseudo = $pseudo;
        if (!$isFirstConnection and !(is_null($login) or $login == "") and !is_null($passwd)) {
            try {
              $member = $this->_memberManager->getByLogin($login);
            } catch (Exception $ex) {
              $member = NULL;
                $this->addMsg("danger", "Login ou mdp incorrect");
            }
            
            if (!is_null($member) and ($member->getPasswd() == $passwd)) {
                $permissionManager = new PermissionManager();
                $guild = (new GuildManager())->getById($member->getGuildInfo()["id"]);
                $_SESSION["id"] = $member->getId();
                $_SESSION["login"] = $login;
                $_SESSION["grade"] = $permissionManager->getGradeById($member->getPermInfo()["id"]);
                $_SESSION["guild"] = Array("id" => $guild->getId(), "name" => $guild->getName(), "color" => $guild->getColor());
                $defaultPage = '?page=raid&subpage=info';
                if ( $_SESSION["grade"] <= $_SESSION["Officier"])
                {
                  $defaultPage = '?page=admin&subpage=dashboard';
                  try {
                    $nb = count((new PendingManager())->getAll());
                    $_SESSION["nbPending"] = $nb;
                  } catch (Exception $ex) {
                  }
                }
                $redirectPage = $_SESSION["redirectUrl"] ?? $defaultPage;
                $_SESSION["redirectUrl"] = NULL;
                header("Location: $redirectPage");
            }
        }
        $v_content_header = $isFirstConnection ? "Enregistrement" : "Connexion";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/connectionPage/ConnectView.php');
    }

    public function disconnect()
    {
        session_destroy();
        unset($_SESSION);
        header('Location: ?page=home');
    }

    public function activeDebug($debugMode)
    {
        if (!is_null($debugMode)) {
            $_SESSION["debug"] = $debugMode;
            header('Location: ?page=home');
        }
        $v_content_header = "Activation du mode Debug";
        $v_title = $this->getTitle();
        $v_msgs = $this->getMsgs();
        require('view/connectionPage/ActiveDebugView.php');
    }


}
