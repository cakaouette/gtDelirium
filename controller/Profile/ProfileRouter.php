<?php
require('ProfileControler.php');
include_once('controller/ConnectControler.php');

if (is_null($_SESSION["id"])) {
  $_SESSION["redirectUrl"] = "?page=profile";
  header('Location: index.php?page=connect');
  exit();
}

$controller = new ProfileControler();

$_SESSION["url"][] = Array("print" => "Profile", "url" => "index.php?page=profile");
$activeTab = "heros";

$update = isset($_POST["updateMemberForm"]);
if ($update) {
  $activeTab = "settings";
  $id = getVar($_POST, "idForm");
  $passwd = getVar($_POST, "oldPasswdForm");
  if ($id == $_SESSION["id"] and $controller->passwdUpdateGrated($id, $passwd.$_SALT)) {
      $newPasswd = getVar($_POST, "newPasswdForm");
      $newPasswd = !empty($newPasswd) ? $newPasswd : $passwd;
      $result = (new ConnectControler())->checkPasswd($newPasswd);
      $login = getVar($_POST, "loginForm");
      if ($result["accept"]) {
        $controller->updateMember($id, $login, md5($newPasswd.$_SALT.$login));
      } else {
        $controller->addErrorPasswd($result["msg"]);
      }
  } else {
      $controller->addErrorPasswd("Mot de passe incorrect");
  }
}



$controller->printPage($_SESSION["id"], true, $activeTab);
return;
