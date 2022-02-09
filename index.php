<?php
session_start();
require('private/indexPrivate.php');

include_once('tools/utils.php');

require('controller/ConnectControler.php');
include_once('model/Manager/PermissionManager.php'); // TODO passer par connect controller

if (!array_key_exists('login', $_SESSION)) {
    $permissionManager = new PermissionManager();
    $_SESSION["login"] = NULL;
    $_SESSION["id"] = NULL;
    $_SESSION["redirectUrl"] = NULL;
    $_SESSION["grade"] = $permissionManager->getGradeByName("Visiteur");
    $_SESSION["guild"] = Array("id" => NULL, "name" => NULL);
    $_SESSION["nbPending"] = NULL;
    try {
        include_once('controller/Raid/RaidControler.php');
        RaidControler::setGuildRaidInfo(NULL, NULL);
    } catch (Exception $e) {
        $_SESSION["raidPreview"] = Array("id" => NULL,
                                      "dateRaid" => NULL);
        $_SESSION["raidInfo"] = Array("id" => NULL,
                                      "dateRaid" => NULL,
                                      "dateNumber" => NULL,
                                      "isFinished" => NULL);
    }

    foreach ($permissionManager->getAllInRawData() as $permId =>$permInfo) {
        $_SESSION[$permInfo["name"]] = $permInfo["grade"];
    }
}

$_SESSION["url"] = [Array("print" => "Home", "url" => "index.php")];
if (isset($_GET['page'])) {
// ---------------------------------------------------------------------------------------------------------------------
// --- BOSS ------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/*    if ($_GET['page'] == 'boss') {
        $_SESSION["url"][] = Array("print" => "Bosses", "url" => "index.php?page=boss");
        require('controller/Boss/BossRouter.php');
        exit();
*/
// ---------------------------------------------------------------------------------------------------------------------
// --- ADMINISTRATION --------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/*    } elseif ($_GET['page'] == 'admin') {
//        $_SESSION["url"][] = Array("print" => "Membre", "url" => "index.php?page=admin&subpage=monitoring");
        require('controller/Admin/AdminRouter.php');
        exit();
*/
// ---------------------------------------------------------------------------------------------------------------------
// --- PROFILE ---------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/*    } elseif ($_GET['page'] == 'profile') {
        require('controller/Profile/ProfileRouter.php');
        exit();
*/
// ---------------------------------------------------------------------------------------------------------------------
// --- API DISCORD -----------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
    if ($_GET['page'] == 'discord') {
        require('controller/Api/ApiRouter.php');
        exit();

// ---------------------------------------------------------------------------------------------------------------------
// --- ALLIANCE --------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/*    } elseif ($_GET['page'] == 'alliance') {
        $_SESSION["url"][] = Array("print" => "Alliance", "url" => "index.php?page=alliance");
        require('controller/Alliance/AllianceRouter.php');
        exit();
*/
// ---------------------------------------------------------------------------------------------------------------------
// --- CONNECT ---------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/*    } elseif ($_GET['page'] == 'connect') {
        $_SESSION["url"][] = Array("print" => "Connexion", "url" => "index.php?page=connect");
        $controller = new ConnectControler();

        $pseudo = getVar($_POST, "pseudoForm");
        $login = getVar($_POST, "loginForm");
        $passwd = getVar($_POST,"passwdForm");
        $checkPwd = is_null($pseudo) ? array("accept" => true, "msg" => "") : $controller->checkPasswd($passwd);
        if (!is_null($pseudo) and !is_null($login) and ($checkPwd["accept"] === true)) {
            $controller->addPending($pseudo, $login, md5($passwd.$_SALT.$login));
        } else {
            $isFirstConnect = (isset($_GET["firstConnect"]) or ($checkPwd["accept"] === false));
            $controller->connect($isFirstConnect, $login, md5($passwd.$_SALT.$login), $pseudo, $checkPwd["msg"]);
        }
        exit();
    } elseif ($_GET['page'] == 'disconnect') {
        $controller = new ConnectControler();
        $controller->disconnect();
        exit();
    } elseif ($_GET['page'] == 'debug') {
        $_SESSION["url"][] = Array("print" => "Debug", "url" => "index.php&page=debug");
        if ($_SESSION["grade"] > $_SESSION["Gestion"]) { header('Location: index.php?page=error&code=403'); }
        $debugMode = isset($_POST["desactiveDebugForm"]);
        $debugMode = !$debugMode && isset($_POST["activeDebugForm"]);
        $debugMode = isset($_POST["debugForm"]) ? $debugMode : NULL;
        $controller = new ConnectControler();
        $controller->activeDebug($debugMode);
        exit();
*/
// ---------------------------------------------------------------------------------------------------------------------
// --- MEMBER ----------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/*    } elseif ($_GET['page'] == 'member') {
        $_SESSION["url"][] = Array("print" => "Membre", "url" => "index.php?page=member&subpage=alliance");
        require('controller/Member/MemberRouter.php');
        exit();
*/
// ---------------------------------------------------------------------------------------------------------------------
// --- RAID ------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
    } elseif ($_GET['page'] == 'raid') {
        $_SESSION["url"][] = Array("print" => "Raid", "url" => "index.php?page=raid&subpage=info");
        if ($_SESSION["grade"] > $_SESSION["Visiteur"]) { header('Location: index.php?page=error&code=403'); };
        require('controller/Raid/RaidRouter.php');
        exit();

// ---------------------------------------------------------------------------------------------------------------------
// --- ERROR -----------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
    } elseif ($_GET['page'] == 'error') {
        $code = getVar($_GET,"code");
        require('view/errorPage/'.$code.'Page.php');
        exit();
// ---------------------------------------------------------------------------------------------------------------------
// --- ASTUCE ----------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/*    } elseif ($_GET['page'] == 'tip') {
        $v_content_header = "Liens & Astuces";
        $v_title = "Astuces";
        require('view/tip/TipLinkView.php');
        exit();*/
// ---------------------------------------------------------------------------------------------------------------------
// --- CONQUEST --------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
    /*} elseif ($_GET['page'] == 'conquest') {
        require('controller/Conquest/ConquestRouter.php');
        exit();*/
    }
}
$v_content_header = "Home";
$v_title = "Delirium - Home";
require('view/template.php');