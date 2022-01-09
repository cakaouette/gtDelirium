<?php
require('AdminControler.php');

if (!isset($_GET['subpage'])) { exit(); }
if ($_SESSION["grade"] > $_SESSION["Officier"]) { header('Location: index.php?page=error&code=403'); exit(); }

$controller = new AdminControler();

// ---------------------------------------------------------------------------------------------------------------------
// --- Heros & Weapons -------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
if ($_GET['subpage'] == 'heros') {
    $_SESSION["url"][] = Array("print" => "Héros & Armes", "url" => "index.php?page=admin&subpage=heros");
    
    $isHeroInsert = isset($_POST["createHeroForm"]);
    $isHeroUpdate = isset($_POST["updateHeroForm"]);
    $isHeroDelete = isset($_GET["delete"]);
    
    if ($isHeroInsert) {
        $controller->submitHero(
            getVar($_POST, "nameForm"),
            getVar($_POST, "gradeForm"),
            getVar($_POST, "elementIdForm"));
    } else if ($isHeroUpdate) {
        $controller->updateHero(
            getVar($_POST, "idForm"),
            getVar($_POST, "nameForm"),
            getVar($_POST, "gradeForm"),
            getVar($_POST, "elementIdForm"));
    } else if ($isHeroDelete) {
        $controller->deleteHero(getVar($_GET, "delete"));
    }
    $controller->printHeroWeapon();
    return;
    
// ---------------------------------------------------------------------------------------------------------------------
// --- TODO ------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
} elseif ($_GET['subpage'] == 'dashboard') {
  $v_title = "TODO";
  $v_content_header = "TODO";
  require('view/template.php');
} elseif ($_GET['subpage'] == 'bosses') {
  $v_title = "TODO";
  $v_content_header = "TODO";
  require('view/template.php');
} elseif ($_GET['subpage'] == 'members') {
  $v_title = "TODO";
  $v_content_header = "TODO";
  require('view/template.php');
}