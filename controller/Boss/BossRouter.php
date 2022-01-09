<?php
require('BossControler.php');

if ($_SESSION["grade"] > $_SESSION["Visiteur"]) { header('Location: index.php?page=error&code=403'); exit();}

$controller = new BossControler();
if (!isset($_GET['subpage'])) {
  $controller->listBosses();
} elseif ($_GET['subpage'] == 'info') {
  $_SESSION["url"][] = Array("print" => "Description", "url" => "index.php?page=boss&subpage=info");
  
  if ($_SESSION["grade"] <= $_SESSION["Joueur"]) {
    $isInsert = false;
    $isUpdate = false;
    if (isset($_POST["updateForm"])) {
      $isInsert = empty(getVar($_POST, "ailmentEnduranceIdForm"));
      $isUpdate = !empty(getVar($_POST, "ailmentEnduranceIdForm"));
    }
    
    if ($isInsert) {
      $controller->addAe(
          $weaponId = getVar($_POST, "weapondIdForm"),
          $bossId = getVar($_POST, "bossIdForm"),
          $rate = getVar($_POST, "rateForm"),
        );
    } elseif ($isUpdate) {
      $controller->updateAe(
          $id = getVar($_POST, "ailmentEnduranceIdForm"),
          $weaponId = getVar($_POST, "weapondIdForm"),
          $bossId = getVar($_POST, "bossIdForm"),
          $rate = getVar($_POST, "rateForm"),
        );
    }
  }
  
  $bossId = getVar($_GET, "id");
  $controller->info($bossId);
}
return;
